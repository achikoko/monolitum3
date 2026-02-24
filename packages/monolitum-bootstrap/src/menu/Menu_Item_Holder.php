<?php

namespace monolitum\bootstrap\menu;

interface Menu_Item_Holder
{

    function openToLeft(): bool;

    function isSubmenu(): bool;

    function isNav(): bool;

}
