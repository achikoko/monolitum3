<?php

namespace monolitum\frontend;

enum LinkHookMode: string
{
    case MODIFY_RECEIVER = "MODIFY_RECEIVER";
    case RENDER_JAVASCRIPT = "RENDER_JAVASCRIPT";
}
