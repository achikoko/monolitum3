<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSColSpanResponsive extends HtmlElementNodeExtension implements BSBuiltIntoInterface

{
    use ResponsiveTrait;

    /**
     * @param BSColSpan|int $def
     */
    public function __construct($def)
    {
        parent::__construct();
        $this->def = is_int($def) ? BSColSpan::of($def) : $def;
    }

    public static function xs(BSColSpan|int $def = 12): static
    {
        return new BSColSpanResponsive($def);
    }

    public function sm(BSColSpan|int $sm): self
    {
        if (is_int($sm)) {
            $this->sm = (BSColSpan::of($sm));
        } else {
            $this->sm = $sm;
        }
        return $this;
    }

    /**
     * @param BSColSpan|int $md
     * @return BSColSpanResponsive
     */
    public function md(BSColSpan|int $md): self
    {
        if (is_int($md)) {
            $this->md = BSColSpan::of($md);
            return $this;
        } else {
            $this->md = $md;
            return $this;
        }
    }

    /**
     * @param BSColSpan|int $lg
     * @return BSColSpanResponsive
     */
    public function lg(BSColSpan|int $lg): self
    {
        if (is_int($lg)) {
            $this->lg = BSColSpan::of($lg);
            return $this;
        } else {
            $this->lg = $lg;
            return $this;
        }
    }

    /**
     * @param BSColSpan|int $xl
     * @return BSColSpanResponsive
     */
    public function xl(BSColSpan|int $xl): self
    {
        if (is_int($xl)) {
            $this->xl = BSColSpan::of($xl);
            return $this;
        } else {
            $this->xl = $xl;
            return $this;
        }
    }

    /**
     * @param BSColSpan|int $xxl
     * @return BSColSpanResponsive
     */
    public function xxl(BSColSpan|int $xxl): self
    {
        if (is_int($xxl)) {
            $this->xxl = BSColSpan::of($xxl);
            return $this;
        } else {
            $this->xxl = $xxl;
            return $this;
        }
    }

    /**
     * @param array<BSColSpanResponsive> $spans
     * @param int $count
     * @return BSColSpanResponsive
     */
    public static function computeComplement(array $spans, $count)
    {

        if (count($spans) === $count)
            return null;

        $def = 0;
        $sm = 0;
        $md = 0;
        $lg = 0;
        $xl = 0;
        $xxl = 0;

        foreach ($spans as $span) {
            $currentValue = 12;
            if ($span->def !== null)
                $currentValue = $span->def->getValue();
            $def += $currentValue;

            $currentValue = 12;
            if ($span->sm !== null)
                $currentValue = $span->sm->getValue();
            $sm += $currentValue;

            $currentValue = 12;
            if ($span->md !== null)
                $currentValue = $span->md->getValue();
            $md += $currentValue;

            $currentValue = 12;
            if ($span->lg !== null)
                $currentValue = $span->lg->getValue();
            $lg += $currentValue;

            $currentValue = 12;
            if ($span->xl !== null)
                $currentValue = $span->xl->getValue();
            $xl += $currentValue;

            $currentValue = 12;
            if ($span->xxl !== null)
                $currentValue = $span->xxl->getValue();
            $xxl += $currentValue;

        }

        $voidCount = $count - count($spans);
        $lastSpan = (12 - $def) / $voidCount;

        $toReturn = BSColSpanResponsive::xs($lastSpan < 12 ? $lastSpan : null);

        $currentSpan = (12 - $sm) / $voidCount;
        if ($currentSpan != $lastSpan)
            $toReturn->sm($currentSpan);
        $lastSpan = $currentSpan;

        $currentSpan = (12 - $md) / $voidCount;
        if ($currentSpan != $lastSpan)
            $toReturn->md($currentSpan);
        $lastSpan = $currentSpan;

        $currentSpan = (12 - $lg) / $voidCount;
        if ($currentSpan != $lastSpan)
            $toReturn->lg($currentSpan);
        $lastSpan = $currentSpan;

        $currentSpan = (12 - $xl) / $voidCount;
        if ($currentSpan != $lastSpan)
            $toReturn->xl($currentSpan);
        $lastSpan = $currentSpan;

        $currentSpan = (12 - $xxl) / $voidCount;
        if ($currentSpan != $lastSpan)
            $toReturn->xxl($currentSpan);

        return $toReturn;
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $this->_buildInto($component, "col",  $inverted);
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

}
