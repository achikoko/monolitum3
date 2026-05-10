<?php

namespace monolitum\database;

use DateTime;
use DateTimeInterface;
use monolitum\core\Find;
use monolitum\core\MNode;
use monolitum\core\panic\DevPanic;
use monolitum\model\attr\Attr;
use monolitum\model\attr\Attr_Bool;
use monolitum\model\attr\Attr_Color;
use monolitum\model\attr\Attr_Date;
use monolitum\model\attr\Attr_DateTime;
use monolitum\model\attr\Attr_Decimal;
use monolitum\model\attr\Attr_Int;
use monolitum\model\attr\Attr_String;
use monolitum\model\AttrExt_Validate_String;
use monolitum\model\EntitiesManager;
use monolitum\model\Entity;
use monolitum\model\EntityPersister;
use monolitum\model\Model;
use monolitum\model\values\Color;
use PDO;

class DatabaseManager extends MNode implements EntityPersister
{

    private PDO $pdo;

    private string $prefix = "";

    private EntitiesManager $entitiesManager;

    public function __construct($builder = null)
    {
        parent::__construct($builder);
    }

    public static function _computeSelectAttrAlias(string $tableAlias, Attr $attr): string
    {
        return $tableAlias . '__' . self::_computeAttrName($attr);
    }

    public static function _computeTableAlias(int $index, bool $subQuery = false): string
    {
        return 'qtbl' . ($subQuery ? 'sq' : '') . '_' . $index;
    }

    public static function _computeAttrName(Attr|string $attr): string
    {
        if($attr instanceof Attr) {
            // Not yet, this means that we need always to check if the attribute has an overwritten name
//            /** @var ?AttrExt_DB $ext */
//            $ext = $attr->findExtension(AttrExt_DB::class);
//            if($ext !== null) {
//                $dbName = $ext->getOverwriteDatabaseName();
//                if($dbName !== null) {
//                    return $dbName;
//                }
//            }
            return $attr->getId();
        }

        return $attr;
    }

    public function setPdo(PDO $pdo): void
    {
        $this->pdo = $pdo;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * @param mixed $value
     * @param Attr $attr
     * @param string $sql
     * @param array $values
     * @return array
     */
    public function appendValue(mixed $value, Attr $attr, string $sign, array &$values): string
    {
        if (is_string($value)) {
            if (!($attr instanceof Attr_String))
                throw new DevPanic("Illegal string value type");
            $sql = " $sign ?";
            $values[] = $value;
        } else if (is_int($value)) {
            if (!($attr instanceof Attr_Int) && !($attr instanceof Attr_Decimal))
                throw new DevPanic("Illegal int value type");
            $sql = " $sign ?";
            $values[] = $value;
        } else if (is_bool($value)) {
            if (!($attr instanceof Attr_Bool))
                throw new DevPanic("Illegal bool value type");
            $sql = " $sign ?";
            $values[] = $value;
        } else if ($value instanceof Color) {
            if (!($attr instanceof Attr_Color))
                throw new DevPanic("Illegal color value type");
            $sql = " $sign ?";
            $values[] = $value->getHexValue();
        } else if ($value instanceof DateTime) {
            if (!($attr instanceof Attr_Date) && !($attr instanceof Attr_DateTime))
                throw new DevPanic("Illegal string value type");
            $sql = " $sign ?";
            $values[] = $value;
        } else {
            $sql = " $sign ?";
            $values[] = $value;
        }
        return $sql;
    }

    /**
     * @param Query_Entities_Executor $query
     * @param string $sql
     * @param array $values
     * @return array
     */
    public function execute_generate_limit(Query_Entities $query, array &$values): string
    {
        $sql = "";
        $low = $query->getLimitLow();
        $high = $query->getLimitMany();

        if ($low !== null && $high !== null) {
            $sql .= " LIMIT ?, ?";
            $values[] = $low;
            $values[] = $high;
        }
        return $sql;
    }

    protected function onBuild(): void
    {
        $this->entitiesManager = Find::pushAndGet(EntitiesManager::class);
        parent::onBuild();
    }

    /**
     * @param class-string|Model $entityModel
     * @return Insert
     */
    public function newInsert(string|Model $entityModel): Insert
    {
        return new Insert($this, $this->entitiesManager->getModel($entityModel));
    }

    public function newUpdate(string|Model $entityModel): Update
    {
        return new Update($this, $this->entitiesManager->getModel($entityModel));
    }

    public function newDelete(string|Model $entityModel): Delete
    {
        return new Delete($this, $this->entitiesManager->getModel($entityModel));
    }

    public function _notifyChanged(Entity $entity, Attr $attr)
    {

    }

    /**
     * @param string[] $array
     * @return string
     * @noinspection SqlNoDataSourceInspection
     */
    public function generateDB(array $array): string
    {

        $sql = "";

        foreach ($array as $modelClass){

            $model = $this->entitiesManager->getModel($modelClass);

            $id = $model->id;
            if($id == "")
                throw new DevPanic("Id is null");

            /** @var Attr[] $ids */
            $ids = [];
            $autoIncrement = null;

            $sql .= "CREATE TABLE " . $this->prefix . $id . "(\n";

            foreach ($model->getAttrs() as $attr) {

                /** @var AttrExt_DB $dbExt */
                $dbExt = $attr->findExtension(AttrExt_DB::class);
                if($dbExt == null)
                    continue;

                $isId = $dbExt->isPrimaryKey();
                if($isId && $autoIncrement != null)
                    throw new DevPanic("Autoincrement models cannot have more than one id");

                $isAutoincrement = $dbExt->isAutoincrement();
                if($isAutoincrement && count($ids) > 0)
                    throw new DevPanic("Autoincrement models cannot have more than one id");
                if($isAutoincrement && !($attr instanceof Attr_Int))
                    throw new DevPanic("Autoincrement must be of type integer");

                if($isAutoincrement){
                    $autoIncrement = $attr;
                }else if($isId){
                    $ids[] = $attr;
                }

                $sql .= "\t" . $attr->getId();

                if($attr instanceof Attr_Int){
                    $sql .= " INT";
                    if($isAutoincrement)
                        $sql .= " AUTO_INCREMENT";
                }else if($attr instanceof Attr_Decimal){
                    $sql .= " INT";
                }else if($attr instanceof Attr_String){
                    /** @var AttrExt_Validate_String $validateString */
                    $validateString = $attr->findExtension(AttrExt_Validate_String::class);
                    $limit = $validateString?->computeMaxChars();

                    // Today I've learned that a varchar type, the limit is in characters
                    // (the x4 is already reserved on the row max size)
                    if($limit == null){
                        $sql .= " TEXT";
                    }else if($limit < 65535){
                        if($validateString?->computeAsciiness()){
                            $sql .= " VARCHAR(" . $limit . ") CHARACTER SET ascii";
                        }else{
                            $sql .= " VARCHAR(" . $limit . ")";
                        }
                    }else if($limit < 16777215){
                        $sql .= " MEDIUMTEXT";
                    }else{
                        $sql .= " LONGTEXT";
                    }

                }else if($attr instanceof Attr_Bool){
                    $sql .= " TINYINT(1)";
                }else if($attr instanceof Attr_Date){
                    $sql .= " DATE";
                }else if($attr instanceof Attr_DateTime){
                    $sql .= " DATETIME";
                }else if($attr instanceof Attr_Color){
                    $sql .= " CHAR(10)";
                }else if($attr instanceof DatabaseableAttr){
                    $sql .= " " . $attr->getDDLType();
                }else {
                    throw new DevPanic("Not recognized type");
                }

                $sql .= ",\n";

            }

            $sql .= "\tPRIMARY KEY(";

            $first = true;
            if($autoIncrement != null){
                $first = false;
                $sql .= $autoIncrement->getId();
            }
            foreach($ids as $id){
                if($first){
                    $first = false;
                }else{
                    $sql .= ", ";
                }
                $sql .= $id->getId();
            }
            $sql .= ")\n";

            $default_charset = 'utf8mb4';
            $default_collation = 'utf8mb4_general_ci';

            $sql .= ") CHARSET " . $default_charset
                . " COLLATE " . $default_collation
                . " ENGINE MyISAM;\n\n";

        }

        return $sql;
    }

    /**
     * @param Delete|Insert|Update $query
     * @return array<int|bool>
     */
    public function executeUpdate(Delete|Update|Insert $query): array
    {
        $model = $query->model;

        $values = [];
        if($query instanceof Update){
            $sql = $this->executeUpdate_Update($query, $model, $values);
        }else if($query instanceof Insert){
            $sql = $this->executeUpdate_Insert($query, $model, $values);
        }else if($query instanceof Delete){
            $sql = $this->executeUpdate_Delete($query, $model, $values);
        }else{
            throw new DevPanic("Query type not supported");
        }

        error_log($sql);

        $stmt = $this->pdo->prepare($sql);

        foreach ($values as $idx => $value){
            if(is_null($value)){
                $stmt->bindValue($idx+1, $value, PDO::PARAM_NULL);
            }else if(is_bool($value)){
                $stmt->bindValue($idx+1, $value, PDO::PARAM_BOOL);
            }else if(is_int($value)){
                $stmt->bindValue($idx+1, $value, PDO::PARAM_INT);
            }else if($value instanceof DateTimeInterface){
                $stmt->bindValue($idx+1, date_format($value, 'Y-m-d\TH:i:s'));
            }else{
                $stmt->bindValue($idx+1, $value);
            }
        }

        $stmt->execute();

        $lastInsert = $this->pdo->lastInsertId();

        $count = $stmt->rowCount();

        $stmt->closeCursor();

        return [$count, $lastInsert !== false ? intval($lastInsert) : false];
    }

    private function executeUpdate_Insert(Insert $query, Model $model, array &$values): string
    {
        $sql = "INSERT INTO " . $this->prefix . $model->id . "(";

        $placeholders = [];
        $count = 0;
        foreach ($query->getValues() as $attrName => $value) {
            if($count > 0)
                $sql .= ",";
            $sql .= "`" . $attrName . "`";
            if($value instanceof Color){
                $values[] = $value->getHexValue();
                $placeholders[] = "?";
            }else{
                $model = $query->model;
                if($model !== null){
                    $attr = $model->getAttr($attrName);
                    if($attr instanceof DatabaseableAttr){
                        $values[] = $attr->getValueForQuery($value);
                        $placeholders[] = $attr->getInsertUpdatePlaceholder();
                    }else{
                        $values[] = $value;
                        $placeholders[] = "?";
                    }
                }else{
                    $values[] = $value;
                    $placeholders[] = "?";
                }
            }
            $count++;
        }

        $sql .= ") VALUES (";

        $first = true;
        foreach($placeholders as $placeholder){
            if($first){
                $first = false;
                $sql .= $placeholder;
            } else {
                $sql .= "," . $placeholder;
            }
        }

        $sql .= ")";

        return $sql;
    }

    /**
     * @param Update $query
     * @param Model $model
     * @param array $values
     * @return string
     */
    private function executeUpdate_Update(Update $query, Model $model, array &$values): string
    {
        $sql = "UPDATE " . $this->prefix . $model->id . " AS " . self::_computeTableAlias(0) . " SET ";

        $count = 0;
        foreach ($query->getValues() as $attrName => $value) {
            if($count > 0)
                $sql .= ",";

            if($value instanceof Color){
                $values[] = $value->getHexValue();
                $placeholder = "?";
            }else{
                $model = $query->model;
                if($model !== null){
                    $attr = $model->getAttr($attrName);
                    if($attr instanceof DatabaseableAttr){
                        $values[] = $attr->getValueForQuery($value);
                        $placeholder = $attr->getInsertUpdatePlaceholder();
                    }else{
                        $values[] = $value;
                        $placeholder = "?";
                    }
                }else{
                    $values[] = $value;
                    $placeholder = "?";
                }
            }

            $sql .= "`" . $attrName . "` = " . $placeholder;

            $count++;
        }

        if($count == 0)
            throw new DevPanic("Update of 0 values");

        $sqlWhere = $this->execute_generate_where_filter($query->getFilter(), $model, self::_computeTableAlias(0), null, $values);
        if(empty($sqlWhere)){
            throw new DevPanic("Execute UPDATE without WHERE statement.");
        }
        $sql .= " WHERE " . $sqlWhere;
        return $sql;
    }

    /**
     * @param Delete $query
     * @param Model $model
     * @param array $values
     * @return string
     * @noinspection SqlNoDataSourceInspection
     */
    private function executeUpdate_Delete(Delete $query, Model $model, array &$values): string
    {
        $sql = "DELETE " . self::_computeTableAlias(0) . " FROM " . $this->prefix . $model->id . " AS "  . self::_computeTableAlias(0);
        $sqlWhere = $this->execute_generate_where_filter($query->getFilter(), $model, self::_computeTableAlias(0), null, $values);
        if(empty($sqlWhere)){
            throw new DevPanic("Execute DELETE without WHERE statement.");
        }
        $sql .= " WHERE " . $sqlWhere;
        return $sql;
    }

    /**
     * @param Query $query
     * @return Query_Result|int|float
     */
    public function executeQuery(Query $query): Query_Result|int|float
    {

//        $entityModel = $this->entitiesManager->getModel($model);
//
//        $attrs2 = [];
//        foreach ($attrs as $attr) {
//            $attrs2[] = $entityModel->getAttr($attr);
//        }

        $model = $this->entitiesManager->getModel($query->model);

        if($query instanceof Query_Entities_Executor){
            if($query->checkParallelSortingRecursive() === null){
                throw new DevPanic("Sorting parallel joins is not supported.");
            }
            // Append ids if you want to update the entity, they are required to run the update
            $sql = $this->execute_generate_select($query, $model);
        }else if($query instanceof Query_Aggregation_Executor){
            $sql = "SELECT " . $query->operation->value . "(`" . self::_computeTableAlias(0) . '`.`' . self::_computeAttrName($model->getAttr($query->selectAttr)) . "`)" ;
        }else{
            throw new DevPanic();
        }

        $sql .= " FROM " . $this->prefix . $model->id . " AS " . self::_computeTableAlias(0);

        if($query->hasJoins()){
            $parentTableCounter = 0;
            $joinsWhereHint = new _JoinsWhereHint($model, self::_computeTableAlias($parentTableCounter));
            $sql .= $this->execute_generate_joins_headers($model, $query, $joinsWhereHint,$parentTableCounter);
        }else{
            $joinsWhereHint = null;
        }

        $values = [];
        $parentTableCounter = 0;
        $whereSql = $this->execute_generate_wheres($query, $model, $joinsWhereHint, $parentTableCounter, $values);
        $parentTableCounter = 0;
        $whereJoinsSql = $this->execute_generate_additional_wheres_for_limited_joins($query, $model, $parentTableCounter, $values);

        if(!empty($whereSql) || !empty($whereJoinsSql)){
            $sql .= " WHERE ";
            if (!empty($whereSql)){
                $sql .= $whereSql;
                if(!empty($whereJoinsSql)){
                    $sql .= " AND " . $whereJoinsSql;
                }
            }else if(!empty($whereJoinsSql)){
                $sql .= $whereJoinsSql;
            }
        }

        if($query instanceof Query_Entities_Executor) {

            $parentTableCounter = 0;
            $orderBySql = $this->execute_generate_order_by($query, $model, $query->hasJoins(), false, true, $parentTableCounter);

            if(!empty($orderBySql)){
                $sql .= " ORDER BY " . $orderBySql;
            }

            if($query->isJoinsLimit1Recursive()) {
                $sql .= $this->execute_generate_limit($query, $values);
            }else if($query->hasLimit()){
                throw new DevPanic("Query with joins without limit 1 cannot have limit.");
            }

            if ($query->isForUpdate())
                $sql .= " FOR UPDATE";
        }

        error_log($sql);

        $stmt = $this->pdo->prepare($sql);

        foreach ($values as $idx => $value){
            if(is_null($value)){
                $stmt->bindValue($idx+1, $value, PDO::PARAM_NULL);
            }else if(is_bool($value)){
                $stmt->bindValue($idx+1, $value, PDO::PARAM_BOOL);
            }else if(is_int($value)){
                $stmt->bindValue($idx+1, $value, PDO::PARAM_INT);
            }else if($value instanceof DateTime){
                $stmt->bindValue($idx+1, date_format($value, 'Y-m-d\TH:i:s'));
            }else{
                $stmt->bindValue($idx+1, $value);
            }
        }

        $stmt->execute();

        if($query instanceof Query_Entities_Executor){
            return new Query_Result($this, $model, $query, $query->isForUpdate(), $stmt);
        }else if($query instanceof Query_Aggregation_Executor){
            $result = $stmt->fetch(PDO::FETCH_NUM)[0];
            $stmt->closeCursor();
            return $result;
        }else{
            throw new DevPanic("unreachable");
        }

    }

    public function execute_generate_select(Query_Entities_Executor $query, Model $model): string
    {
        $sql = "SELECT ";

        $sql .= $this->execute_generate_select_append_model_attrs(
            $model,
            $query->isForUpdate() || $query->hasJoins(),
            $query->getSelectAttrs(),
            self::_computeTableAlias(0)
        );

        if($query->hasJoins()) {
            $parentTableCounter = 0;
            $sql .= $this->execute_generate_select_joins($query, $parentTableCounter);
        }

        return $sql;
    }

    public function execute_generate_select_joins(Query_Entities $query, int &$parentTableCounter): string
    {
        $sql = "";

        /** @var Query_Join_Tuple $joinTuple */
        foreach ($query->getJoins() as $joinTuple){
            $join = $joinTuple->join;
            $model = $this->entitiesManager->getModel($join->model);
            $tableId = ++$parentTableCounter;

            $sql .= $this->execute_generate_select_append_model_attrs(
                $model,
                true,
                $join->getSelectAttrs(),
                self::_computeTableAlias($tableId),
                true
            );

            if($join->hasJoins()) {
                $sql .= $this->execute_generate_select_joins($join, $parentTableCounter);
            }

        }

        return $sql;
    }

    private function execute_generate_select_append_model_attrs(Model $entity, bool $idsMandatory, array|bool $selectedAttrsIds, string $tablePrefix, bool $appendInitialComma = false): string
    {
        $string = "";
        foreach ($entity->getAttrs() as $attr) {
            /** @var AttrExt_DB $ext */
            $ext = $attr->findExtension(AttrExt_DB::class);
            if (
                $ext != null
                && ($selectedAttrsIds === true
                    || $idsMandatory && $ext->isPrimaryKey()
                    || is_array($selectedAttrsIds) && in_array($attr->getId(), $selectedAttrsIds)
                )) {
                if($appendInitialComma || !empty($string)){
                    $string .= ", ";
                }
                $string .= '`' . $tablePrefix . '`.`' . self::_computeAttrName($attr)
                    . '` AS ' . self::_computeSelectAttrAlias($tablePrefix, $attr);
            }
        }
        return $string;
    }

    public function execute_generate_additional_wheres_for_limited_joins(Query $query, Model $parentModel, int &$parentTableCounter, array &$values): string
    {

        $sql = "";
        $parentTableAlias = self::_computeTableAlias($parentTableCounter);

        if($query->hasJoins()) {
            $count = 0;
            foreach ($query->getJoins() as $joinTuple){
                $join = $joinTuple->join;
                if($join->isLimit1Recursive()){
                    $childModel = $this->entitiesManager->getModel($join->model);
                    $parentTableCounter++;
                    $childTableAlias = self::_computeTableAlias($parentTableCounter);

                    if($count++ > 0){
                        $sql .= " AND ";
                    }

                    $sql .= "(";
                    $sql .= $this->createListOfTableKeys($childModel, $childTableAlias, true);
                    $sql .= " = (SELECT ";
                    $childTableSubqueryAlias = self::_computeTableAlias($parentTableCounter, true);
                    $sql .= $this->createListOfTableKeys($childModel, $childTableSubqueryAlias, false);
                    $sql .= $this->execute_generate_join(
                        $joinTuple->attrs, $parentModel, $parentTableAlias,
                        $join->getLocalJointAttrs(), $childModel, $childTableSubqueryAlias,
                        "FROM", "WHERE"
                    );

                    $filter = $join->getFilter();
                    if($filter != null){
                        $sql .= " AND " . $this->execute_generate_where_filter($filter, $childModel, $childTableSubqueryAlias, null, $values);
                    }

                    $orderBySql = "";
                    $sortedAttrs = $join->getSortedAttrs();
                    if (!empty($sortedAttrs)) {
                        foreach ($sortedAttrs as $sortedAttr) {
                            if (!empty($orderBySql))
                                $orderBySql .= ", ";
                            $orderBySql .= "`" . $childTableSubqueryAlias . "`.`" . self::_computeAttrName($sortedAttr->attr) . "` " . ($sortedAttr->desc ? "DESC " : "ASC ");
                        }
                    }

                    if(!empty($orderBySql)){
                        $sql .= " ORDER BY " . $orderBySql;
                    }
                    $sql .= $this->execute_generate_limit($join, $values);

                    $sql .= ")";

                    if($joinTuple->outer){
                        // Append or NULL
                        $sql .= " OR ";
                        $and = [];
                        foreach ($childModel->getAttrs() as $attr) {
                            /** @var ?AttrExt_DB $ext */
                            $ext = $attr->findExtension(AttrExt_DB::class);
                            if($ext !== null && $ext->isPrimaryKey()){
                                $and[$attr->getId()] = null;
                            }
                        }
                        $sql .= $this->execute_generate_where_list($and, $childModel, $childTableAlias, null, $values);
                    }

                    $sql .= ")";

                    $gen = $this->execute_generate_additional_wheres_for_limited_joins($join, $childModel, $parentTableCounter, $values);
                    if(!empty($gen)){
                        $sql .= " AND " . $gen;
                    }
                }else{
                    if($join->hasPromotedSortingRecursive()){
                        throw new DevPanic("Found a promoted sorting inside a non limit 1 join.");
                    }
                }
            }
        }

        return $sql;
    }

    private function createListOfAttrs(string $tableAlias, array $attrs, bool $parens): string
    {
        $string = "";
        $count = 0;
        foreach ($attrs as $attr) {
            $attr = self::_computeAttrName($attr);
            if($count > 0){
                $string .= ", ";
            }
            $string .= $tableAlias . "." . $attr;
            $count++;
        }

        if($parens && $count > 1){
            return "(" . $string . ")";
        }else{
            return $string;
        }

    }

    private function createListOfTableKeys(Model $model, string $tableAlias, bool $parens): string
    {
        $string = "";
        $count = 0;
        foreach ($model->getAttrs() as $attr) {
            /** @var ?AttrExt_DB $ext */
            $ext = $attr->findExtension(AttrExt_DB::class);
            if($ext !== null && $ext->isPrimaryKey()){
                if($count > 0){
                    $string .= ", ";
                }
                $string .= $tableAlias . "." . $attr->getId();
                $count++;
            }
        }

        if($parens && $count > 1){
            return "(" . $string . ")";
        }else{
            return $string;
        }

    }

    public function execute_generate_wheres(Query $query, Model $model, ?_JoinsWhereHint $joinsWhereHint, int &$parentTableCounter, array &$values): string
    {

        $sql = "";

        $filter = $query->getFilter();
        if($filter != null){
            $sql .= $this->execute_generate_where_filter($filter, $model, self::_computeTableAlias($parentTableCounter), $joinsWhereHint, $values);
        }

        if($query->hasJoins()) {
            foreach ($query->getJoins() as $joinTuple){
                $join = $joinTuple->join;

                // Limit1 join where's are described in the subquery
                if(!$join->isLimit1Recursive()) {
                    $model = $this->entitiesManager->getModel($join->model);
                    $parentTableCounter++;
                    $whereSql = $this->execute_generate_wheres($join, $model, $joinsWhereHint, $parentTableCounter, $values);
                    if (!empty($whereSql)) {
                        if (!empty($sql))
                            $sql .= " AND ";
                        $sql .= $whereSql;
                    }
                }
            }
        }

        return $sql;
    }

    public function execute_generate_where_filter(array|Query_Or|null $filter, Model $model, string $tableAlias, ?_JoinsWhereHint $joinsWhereHint, array &$values, bool $forceParenthesis = false): string
    {

        $sql = "";

        if($filter === null){
            return $sql;
        }

        if(is_array($filter)){
            // parse and
            $sql .= $this->execute_generate_where_list($filter, $model, $tableAlias, $joinsWhereHint, $values, forceParenthesis: $forceParenthesis);
        }else if($filter instanceof Query_Or){
            $sql .= $this->execute_generate_where_list($filter->getFilters(), $model, $tableAlias, $joinsWhereHint, $values, "OR", true);
        }

        return $sql;
    }

    public function execute_generate_where_list(array $filters, Model $model, string $tableAlias, ?_JoinsWhereHint $joinsWhereHint, array &$values, string $operation = "AND", bool $forceParenthesis = false): string
    {

        $sql = "";

        $first = true;
        foreach ($filters as $attrId => $filter){
            if(is_string($attrId) && $attrId !== ""){
                $attr = $model->getAttr($attrId);
                $ext = $attr->findExtension(AttrExt_DB::class);
                if ($ext != null) {
                    if ($first) {
                        $first = false;
                        if($forceParenthesis)
                            $sql .= "(";
                    }else {
                        $sql .= " $operation ";
                    }

                    $sql .= $this->execute_generate_where_attr($attr, $filter, $model, $tableAlias, $joinsWhereHint, $values) . " ";

                }
            } else if($filter instanceof Query_JoinedModel) {
                $childModel = $this->entitiesManager->getModel($filter->model);
                if($joinsWhereHint !== null && array_key_exists($childModel->id, $joinsWhereHint->joinsWhereHintsByModel)){
                    $childJoinsWhereHint = $joinsWhereHint->joinsWhereHintsByModel[$childModel->id];
                    $childTable = $childJoinsWhereHint->tableAlias;
                    $childModel = $childJoinsWhereHint->model;
                    $sql2 = $this->execute_generate_where_list($filter->getFilters(), $childModel, $childTable, $childJoinsWhereHint, $values);

                    if($sql2 !== ""){
                        if ($first) {
                            $first = false;
                            if($forceParenthesis)
                                $sql .= "(";
                        }else {
                            $sql .= " $operation ";
                        }
                        $sql .= $sql2;
                    }

                }else{
                    throw new DevPanic("Joined table $filter->model not found in query.");
                }

            } else {
                $sql2 = $this->execute_generate_where_filter($filter, $model, $tableAlias, $joinsWhereHint, $values);

                if($sql2 !== ""){
                    if ($first) {
                        $first = false;
                        if($forceParenthesis)
                            $sql .= "(";
                    }else {
                        $sql .= " $operation ";
                    }
                    $sql .= $sql2;
                }
            }

        }

        if(!$first){
            if($forceParenthesis)
                $sql .= ")";
        }

        return $sql;
    }

    private function execute_generate_where_attr(Attr $attr, mixed $filter, Model $model, string $tableAlias, ?_JoinsWhereHint $joinsWhereHint, array &$values): string
    {

        $sql = $tableAlias . "." . $attr->getId();

        if($filter === null){
            $sql .= " IS NULL";
        }else if($filter instanceof Query_NotNull) {
            $sql .= " IS NOT NULL";
        }else if($filter instanceof Query_CMP){
            $sql .= " IS NOT NULL AND " . $tableAlias . "." . $attr->getId();
            $value = $filter->value;
            $sign = $filter->sign;
            if(is_int($value)){
                if(!($attr instanceof Attr_Int) && !($attr instanceof Attr_Decimal))
                    throw new DevPanic("Illegal int value type");
                $sql .= " " . $sign . " ?";
                $values[] = $value;
            }else if($value instanceof DateTime){
                if(!($attr instanceof Attr_Date) && !($attr instanceof Attr_DateTime))
                    throw new DevPanic("Illegal datetime value type");
                $sql .= " " . $sign . " ?";
                $values[] = $value;
            }
        }else if($filter instanceof Query_Like){

            $string = $filter->getString();

            $processedString = "";

            $pos = 0;
            foreach ($filter->getParams() as $param){

                $param = str_replace("\'", "\\\\'", $param);
                $param = str_replace("!", "!!", $param);
                $param = str_replace("%", "!%", $param);
                $param = str_replace("_", "!_", $param);

                $nextExclamation = strpos($string, "?", $pos);

                if($nextExclamation > $pos)
                    $processedString .= substr($string, $pos, $nextExclamation-$pos);

                $processedString .= $param;

                $pos = $nextExclamation+1;

            }

            $processedString .= substr($string, $pos, strlen($string)-$pos);

            $sql .= " LIKE ? ESCAPE '!' ";
            $values[] = $processedString;

        }else if($filter instanceof Query_Different){
            $sql .= $this->appendValue($filter->value, $attr, "<>", $values);
        } else if(is_array($filter)){
            if($joinsWhereHint !== null && array_key_exists(self::_computeAttrName($attr), $joinsWhereHint->joinsWhereHintsByAttr)){
                $childJoinsWhereHint = $joinsWhereHint->joinsWhereHintsByAttr[self::_computeAttrName($attr)];
                $childModel = $joinsWhereHint->model;
                $childTable = $joinsWhereHint->tableAlias;
                $this->execute_generate_where_list($filter, $childModel, $childTable, $childJoinsWhereHint, $values );
            }else{
                throw new DevPanic("Joined table not found in query.");
            }
        } else {
            $sql .= $this->appendValue($filter, $attr, "=", $values);
        }

        return $sql;

    }

    public function execute_generate_order_by(Query_Entities $query, Model $model, bool $orderByIds, bool $appendInitialComma, bool $isRoot, int &$parentTableCounter): string
    {

        $sql = "";
        $tableAlias = self::_computeTableAlias($parentTableCounter);

        $sortedAttrs = $query->getSortedAttrs();
        if (!empty($sortedAttrs)) {
            foreach ($sortedAttrs as $sortedAttr) {
//                if($isRoot || $sortedAttr->promoteToGlobalDesc !== null) {
                    if ($appendInitialComma || !empty($sql))
                        $sql .= ", ";
                    $sql .= "`" . $tableAlias
                        . "`.`" . self::_computeAttrName($sortedAttr->attr)
                        . "` " . (($sortedAttr->promoteToGlobalDesc !== null ? $sortedAttr->promoteToGlobalDesc : $sortedAttr->desc) ? "DESC " : "ASC ");
//                }
            }
        }

        if(!$isRoot && $orderByIds){
            foreach ($model->getAttrs() as $attr) {
                /** @var ?AttrExt_DB $ext */
                $ext = $attr->findExtension(AttrExt_DB::class);
                if ($ext !== null && $ext->isPrimaryKey()) {
                    if($appendInitialComma || !empty($sql)){
                        $sql .= ", ";
                    }
                    $sql .= "`" . $tableAlias . "`.`" . self::_computeAttrName($attr) . "` ASC";
                }
            }
        }

        if($query->hasJoins()) {
            foreach ($query->getJoins() as $joinTuple){
                $join = $joinTuple->join;
                $model = $this->entitiesManager->getModel($join->model);
                $parentTableCounter++;
                $sql .= $this->execute_generate_order_by($join, $model, $orderByIds, $appendInitialComma || !empty($sql), false, $parentTableCounter);
            }
        }

        return $sql;
    }
    /**
     * @param Entity $entity
     * @return array
     */
    public function generate_ids_filter(Entity $entity): array
    {
        $ids = [];
        foreach ($entity->getModel()->getAttrs() as $attr) {
            /** @var AttrExt_DB $ext */
            $ext = $attr->findExtension(AttrExt_DB::class);
            if ($ext !== null && $ext->isPrimaryKey())
                $ids[$attr->getId()] = $entity->getValue($attr);
        }
        return $ids;
    }

    private function execute_generate_joins_headers(Model $model, Query $query, ?_JoinsWhereHint $whereHint, int &$parentTableCounter): string
    {
        $sql = "";
        $parentTableAlias = self::_computeTableAlias($parentTableCounter);

        foreach($query->getJoins() as $joinTuple){
            $childTableAliasCounter = ++$parentTableCounter;
            $join = $joinTuple->join;

            /** @var array<string|Attr> $leftAttrs */
            $leftAttrs = $joinTuple->attrs;

            $childModel = $this->entitiesManager->getModel($join->model);
            $rightAttrs = $join->getLocalJointAttrs();

            if($joinTuple->outer){
                $sql .= " LEFT";
            }

            $childTableAlias = self::_computeTableAlias($childTableAliasCounter);

            $sql .= $this->execute_generate_join(
                $leftAttrs, $model, $parentTableAlias,
                $rightAttrs, $childModel, $childTableAlias
            );

            $childJoinsWhereHint = new _JoinsWhereHint($childModel, $childTableAlias);

            if($join->hasJoins()){
                $sql .= $this->execute_generate_joins_headers($childModel, $join, $childJoinsWhereHint, $parentTableCounter);
            }

            if(sizeof($leftAttrs) == 1 && !array_key_exists($leftAttrs[0], $whereHint->joinsWhereHintsByAttr)){
                $whereHint->joinsWhereHintsByAttr[self::_computeAttrName($leftAttrs[0])] = $childJoinsWhereHint;
            }

            if(!array_key_exists($childModel->id, $whereHint->joinsWhereHintsByModel)){
                $whereHint->joinsWhereHintsByModel[$childModel->id] = $childJoinsWhereHint;
            }
        }

        return $sql;
    }

//    private function execute_generate_joins_where(Model $model, array $joins, int &$table_code, array &$values): string
//    {
//        $sql = "";
//
//        foreach($joins as $joinObject){
//            $table_code++;
//
//            /** @var Query_Join $join */
//            $join = $joinObject["join"];
//
//            $childModel = $this->entitiesManager->getModel($join->model);
//
//            $sql .= $this->execute_generate_where_filter($join->getFilter(), $childModel, self::_computeTableAlias($table_code), $values);
//
//            $childJoins = $join->getJoins();
//            if(sizeof($childJoins) > 0){
//                $sql .= $this->execute_generate_joins_where($childModel, $childJoins, $table_code, $values);
//            }
//
//        }
//
//        return $sql;
//    }

    private function execute_generate_join(string|array $leftAttrs, Model $leftModel, string $leftModelAlias, array $rightAttrs, Model $rightModel, string $rightModelAlias, string $joinString = "JOIN", string $onString = "ON"): string
    {
        $sql = " $joinString " . $this->prefix . $rightModel->id . " AS " . $rightModelAlias . " $onString";

        for ($i = 0; $i < sizeof($leftAttrs); $i++) {
            $leftAttr = $leftAttrs[$i];
            $rightAttr = $rightAttrs[$i];

            if($leftAttr instanceof Attr){
                $leftAttr = $leftAttr->getId();
            }

            if($rightAttr instanceof Attr){
                $rightAttr = $rightAttr->getId();
            }

            if ($i > 0){
                $sql .= " AND";
            }

            $sql .= " " . $leftModelAlias . "." . $leftAttr . " = " . $rightModelAlias . "." . $rightAttr;
        }

        return $sql;
    }


    public function _executeInsertEntity(Entity $entity): array
    {

        $model = $entity->getModel();

        $query = $this->newInsert($model);

        // TODO check autoincrement is null and rest of ids are not null

        // Default values
        foreach ($model->getAttrs() as $attr){
            /** @var ?AttrExt_DB $databaseExt */
            $databaseExt = $attr->findExtension(AttrExt_DB::class);
            if($databaseExt !== null && $databaseExt->isDefaultSet()){
                $query->addValue($attr, $databaseExt->getDef());
            }
        }

        foreach ($entity->getUpdateAttrs() as $attrName => $value){
            $attr = $model->getAttr($attrName);
            /** @var ?AttrExt_DB $databaseExt */
            $databaseExt = $attr->findExtension(AttrExt_DB::class);
            if($databaseExt !== null){
                $query->addValue($attrName, $value);
            }
        }

        return $query->execute();

    }

    public function _executeUpdateEntity(Entity $entity): array
    {
        $model = $entity->getModel();

        $query = $this->newUpdate($model);

        foreach ($entity->getUpdateAttrs() as $attrName => $value){
            $attr = $model->getAttr($attrName);
            /** @var ?AttrExt_DB $databaseExt */
            $databaseExt = $attr->findExtension(AttrExt_DB::class);
            if($databaseExt !== null){
                $query->addValue($attrName, $value);
            }
        }

        $ids = $this->generate_ids_filter($entity);
        $query->filter($ids);

        return $query->execute();

    }

    public function _executeDeleteEntity(Entity $entity): int
    {
        $query = $this->newDelete($entity->getModel());

        $ids = $this->generate_ids_filter($entity);
        $query->filter($ids);

        return $query->execute();
    }

    public function _notifyEntityChanged(Entity $entity): void
    {
        // TODO: Implement _notifyEntityChanged() method.
    }

}

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */
class _JoinsWhereHint {

    public function __construct(public readonly Model $model, public readonly string $tableAlias)
    {

    }

    /** @var array<string, _JoinsWhereHint>  */
    public array $joinsWhereHintsByAttr = [];

    /** @var array<string, _JoinsWhereHint>  */
    public array $joinsWhereHintsByModel = [];

}
