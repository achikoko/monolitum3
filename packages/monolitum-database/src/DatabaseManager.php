<?php

namespace monolitum\database;

use DateTime;
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

    public function newQuery(string|Model $entityModel): Query_Entities_Executor
    {
        return new Query_Entities_Executor($this, $this->entitiesManager->getModel($entityModel));
    }

    public function newQuery_Aggregation(string|Model $entity, string|Attr $attr, string $operation): Query_Aggregation_Executor
    {
        $model = $this->entitiesManager->getModel($entity);
        return new Query_Aggregation_Executor($this, $model, $model->getAttr($attr), $operation);
    }

    public function newJoin(Model|string $entity, array|string $attrs): Join
    {
        $entityModel = $this->entitiesManager->getModel($entity);

        $attrs2 = [];
        foreach ($attrs as $attr) {
            $attrs2[] = $entityModel->getAttr($attr);
        }

        return new Join($this, $entityModel, $attrs2);
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

                    if($limit == null){
                        $sql .= " TEXT";
                    }else if($limit < 65535/4){
                        if($validateString?->computeAsciiness()){
                            $sql .= " VARCHAR(" . $limit . ") CHARACTER SET ascii";
                        }else{
                            $sql .= " VARCHAR(" . intval($limit*4) . ")";
                        }
                    }else if($limit < 16777215/4){
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

    public function execute_generate_select(Query_Entities $query, Model $model, array &$selectAttr): string
    {
        $sql = "SELECT ";

        $selectAttrIds = $query->getSelectAttrs();
        $first = true;

        // Append existing select attrs
        foreach ($selectAttr as $attr) {
            $ext = $attr->findExtension(AttrExt_DB::class);
            if ($ext != null) {
                if ($first)
                    $first = false;
                else
                    $sql .= ", ";
                $sql .= '`' . $attr->getId() . '`';
            }
        }

        if ($selectAttrIds == null) {
            foreach ($model->getAttrs() as $attr) {
                $ext = $attr->findExtension(AttrExt_DB::class);
                if ($ext != null) {
                    if ($first)
                        $first = false;
                    else
                        $sql .= ", ";
                    $sql .= '`' . $attr->getId() . '`';
                    $selectAttr[] = $attr;
                }
            }
        } else {
            foreach ($selectAttrIds as $attrId) {
                $attr = $model->getAttr($attrId);
                $ext = $attr->findExtension(AttrExt_DB::class);
                if ($ext != null) {
                    if ($first)
                        $first = false;
                    else
                        $sql .= ", ";
                    $sql .= '`' . $attr->getId() . '`';
                    $selectAttr[] = $attr;
                }
            }
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
            }else if($value instanceof DateTime){
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

    /** @noinspection SqlNoDataSourceInspection */
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
        $sql = "UPDATE " . $this->prefix . $model->id . " AS qtbl_0 SET ";

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

        $sql .= $this->execute_generate_where($query->getFilter(), $model, "qtbl_0", $values);

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
        $sql = "DELETE qtbl_0 FROM " . $this->prefix . $model->id . " AS qtbl_0";
        $sql .= $this->execute_generate_where($query->getFilter(), $model, "qtbl_0", $values);
        return $sql;
    }

    /**
     * @param Query $query
     * @return Query_Result|int|float
     */
    public function executeQuery(Query $query): Query_Result|int|float
    {

        $model = $query->model;

        $selectAttrs = [];
        if($query instanceof Query_Entities_Executor){
            // Append ids if you want to update the entity, they are required to run the update
            if($query->isForUpdate())
                $this->append_model_ids($model, $selectAttrs);

            $sql = $this->execute_generate_select($query, $model, $selectAttrs);
        }else if($query instanceof Query_Aggregation_Executor){
            $sql = "SELECT " . $query->operation . "(`" . $query->selectAttr->getId() . "`)" ;
        }else{
            throw new DevPanic();
        }

        $sql .= " FROM " . $this->prefix . $model->id . " AS qtbl_0";

        $joins = $query->getJoins();
        if(sizeof($joins) > 0){
            $table_code = 1;
           $this->execute_generate_joins_header($model, $joins, "qtbl_0", $table_code);
        }

        $values = [];
        $sql .= $this->execute_generate_where($query->getFilter(), $model, "qtbl_0", $values);

        if(sizeof($joins) > 0){
            $table_code = 1;
            $this->execute_generate_joins_where($model, $joins, $table_code, $values);
        }

        $sortedAttrs = $query->getSortedAttrs();
        if($sortedAttrs){
            $sql .= " ORDER BY ";
            $sortedAttrsAsc = $query->getSortedAttrsAsc();

            $i = 0;
            foreach ($sortedAttrs as $sortedAttr){
                if($i > 0)
                    $sql .= ",";
                $sql .= " " . $sortedAttr . " " . ($sortedAttrsAsc[$i] ? "ASC " : "DESC ");
                $i++;
            }

        }

        $low = $query->getLimitLow();
        $high = $query->getLimitMany();

        if($low !== null && $high !== null){
            $sql .= " LIMIT ?, ?";
            $values[] = $low;
            $values[] = $high;
        }

        if($query instanceof Query_Entities_Executor){
            if($query->isForUpdate())
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
            return new Query_Result($this, $model, $selectAttrs, $query->isForUpdate(), $stmt);
        }else if($query instanceof Query_Aggregation_Executor){
            $result = $stmt->fetch(PDO::FETCH_NUM)[0];
            $stmt->closeCursor();
            return $result;
        }else{
            throw new DevPanic("unreachable");
        }

    }

    public function execute_generate_where(array|Query_Or|null $filter, Model $model, string $alias, array &$values): string
    {

        $sql = "";

        if($filter != null){

            $sql .= $this->execute_generate_where_filter($filter, $model, $alias, $values);

            if($sql == ""){
                return $sql;
            }else{
                return " WHERE " . $sql;
            }

        }

        return $sql;
    }

    public function execute_generate_where_filter(array|Query_Or $filter, Model $model, string $alias, array &$values): string
    {

        $sql = "";

        if(is_array($filter)){
            // parse and
            $sql .= $this->execute_generate_where_list($filter, $model, $alias, $values);
        }else if($filter instanceof Query_Or){

            $or = $this->execute_generate_where_list($filter->getFilters(), $model, $alias, $values, "OR");

            if($or !== ""){
                $sql .= "($or)";
            }
        }

        return $sql;
    }

    public function execute_generate_where_list(array $filters, Model $model, string $alias, array &$values, string $operation = "AND"): string
    {

        $sql = "";

        $first = true;
        foreach ($filters as $attrId => $filter){
            if(is_string($attrId) && $attrId !== ""){
                $attr = $model->getAttr($attrId);
                $ext = $attr->findExtension(AttrExt_DB::class);
                if ($ext != null) {
                    if ($first)
                        $first = false;
                    else
                        $sql .= " $operation ";

                    $sql .= $this->execute_generate_where_attr($attr, $filter, $model, $alias, $values);

                }
            }else {
                $sql2 = $this->execute_generate_where_filter($filter, $model, $alias, $values);

                if($sql2 !== ""){
                    if ($first)
                        $first = false;
                    else
                        $sql .= " $operation ";
                    $sql .= $sql2;
                }
            }
        }

        return $sql;
    }

    private function execute_generate_where_attr(Attr $attr, mixed $filter, Model $model, string $alias, array &$values): string
    {

        $sql = $alias . "." . $attr->getId();

        if($filter === null){
            $sql .= " IS NULL ";
        }else if($filter instanceof Query_NotNull) {
            $sql .= " IS NOT NULL ";
        }else if($filter instanceof Query_CMP){
            $sql .= " IS NOT NULL AND " . $alias . "." . $attr->getId();
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
        }else {
            $sql .= $this->appendValue($filter, $attr, "=", $values);
        }

        return $sql;

    }

    public function _executeInsertEntity(Entity $entity): array
    {

        $query = $this->newInsert($entity->getModel());

        // TODO check autoincrement is null and rest of ids are not null

        // Default values
        foreach ($entity->getModel()->getAttrs() as $attr){
            /** @var ?AttrExt_DB $databaseExt */
            $databaseExt = $attr->findExtension(AttrExt_DB::class);
            if($databaseExt !== null && $databaseExt->isDefaultSet()){
                $query->addValue($attr, $databaseExt->getDef());
            }
        }

        foreach ($entity->getUpdateAttrs() as $attrName => $value){
            $query->addValue($attrName, $value);
        }

        return $query->execute();

    }

    public function _executeUpdateEntity(Entity $entity): array
    {
        $query = $this->newUpdate($entity->getModel());

        foreach ($entity->getUpdateAttrs() as $attrName => $value){
            $query->addValue($attrName, $value);
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

    public function append_model_ids(Model $entity, array &$selectAttrs): array
    {
        $ids = [];
        foreach ($entity->getAttrs() as $attr) {
            if(in_array($attr, $selectAttrs))
                continue;

            /** @var AttrExt_DB $ext */
            $ext = $attr->findExtension(AttrExt_DB::class);
            if ($ext !== null && $ext->isPrimaryKey())
                $selectAttrs[] = $attr;
        }
        return $ids;
    }

    private function execute_generate_joins_header(Model $model, array $joins, string $parentAlias, int &$table_code): string
    {
        $sql = "";

        foreach($joins as $joinObject){
            $table_code++;

            /** @var array<string|Attr> $leftAttrs */
            $leftAttrs = $joinObject["attrs"];
            /** @var Join $join */
            $join = $joinObject["join"];

            $childModel = $join->model;
            $rightAttrs = $join->getLocalAttrs();

            $sql .= $this->execute_generate_join($leftAttrs, $model, $parentAlias, $childModel, "tbl_" . $table_code, $rightAttrs);

            $childJoins = $join->getJoins();
            if(sizeof($childJoins) > 0){
                $sql .= $this->execute_generate_joins_header($childModel, $childJoins, "qtbl_" . $table_code, $table_code);
            }

        }

        return $sql;
    }

    private function execute_generate_joins_where(Model $model, array $joins, int &$table_code, array &$values): string
    {
        $sql = "";

        foreach($joins as $joinObject){
            $table_code++;

            /** @var Join $join */
            $join = $joinObject["join"];

            $childModel = $join->model;

            $sql .= $this->execute_generate_where($join->getFilter(), $childModel, "qtbl_" . $table_code, $values);

            $childJoins = $join->getJoins();
            if(sizeof($childJoins) > 0){
                $sql .= $this->execute_generate_joins_where($childModel, $childJoins, $table_code, $values);
            }

        }

        return $sql;
    }

    private function execute_generate_join(string|array $leftAttrs, Model $leftModel, string $leftModelAlias, Model $rightModel, string $rightModelAlias, array $rightAttrs): string
    {
        $sql = " JOIN " . $rightModel->id . " AS " . $rightModelAlias . " ON";

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

}
