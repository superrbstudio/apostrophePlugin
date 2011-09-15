<?php

/**
 * One way to override the way we store sessions by default is to override
 * this class. (Sites not started from newer versions of our sandbox are probably
 * still using plain vanilla sfSessionStorage, which is OK for most cases.)
 */
class aCacheSessionStorage extends sfCacheSessionStorage
{
}
