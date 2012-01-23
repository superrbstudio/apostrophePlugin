<?php

/**
 * Interface to be implemented by all implementations of
 * resource locking for Apostrophe. Apostrophe uses resource
 * locks for mission critical purposes such as ensuring the
 * page tree is not critically damaged by simultaneous
 * alterations, so all locking implementations must be
 * stable and robust with no race conditions. 
 */
 
interface aLock
{
  /**
   * All lock implementations should accept an array of
   * implementation-specific options. This facilitates
   * injecting those options via app_a_lock_options.
   * @param array $options
   */
   
  public function __construct($options);
  
  /**
   * The lock name must be non-empty and must be made up
   * entirely of ASCII alphanumeric characters and underscores.
   *
   * Returns true if the resource is successfully locked
   * exclusively to this PHP request, otherwise false.
   * If $wait is true, waits 30 seconds for the resource
   * to be available, then throws an sfException. If
   * $wait is false, returns true or false immediately based
   * on whether the lock could be obtained immediately or not.
   * "Immediately" should be interpreted reasonably for 
   * a given implementation; of course it can take time
   * to be reasonably certain that you got a lock over a
   * network. But in no event should it wait more than
   * 5 seconds without returning failure.
   */
  public function lock($name, $wait = true);
  
  /**
   * Releases the most recently obtained lock, if any,
   * belonging to this PHP request.
   * 
   * It is NOT an error to call unlock when you do not
   * currently hold any locks. This simplifies cleanup
   * in many cases.
   */
  public function unlock();
}
