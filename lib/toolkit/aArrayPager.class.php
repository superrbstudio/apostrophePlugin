<?php
/**
 * 
 * @author Scott Meves
 * http:snippets.symfony-project.org/snippet/177
 * @package    apostrophePlugin
 * @subpackage    toolkit
 */
class aArrayPager extends sfPager
{
  protected $resultsArray = null;

  /**
   * DOCUMENT ME
   * @param mixed $class
   * @param mixed $maxPerPage
   */
  public function __construct($class = null, $maxPerPage = 10)
  {
    parent::__construct($class, $maxPerPage);
  }

  /**
   * DOCUMENT ME
   */
  public function init()
  {
    $this->setNbResults(count($this->resultsArray));
 
    if (($this->getPage() == 0 || $this->getMaxPerPage() == 0))
    {
     $this->setLastPage(0);
    }
    else
    {
     $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $array
   */
  public function setResultArray($array)
  {
    $this->resultsArray = $array;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getResultArray()
  {
    return $this->resultsArray;
  }

  /**
   * DOCUMENT ME
   * @param mixed $offset
   * @return mixed
   */
  public function retrieveObject($offset) {
    return $this->resultsArray[$offset];
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getResults()
  {
    return array_slice($this->resultsArray, ($this->getPage() - 1) * $this->getMaxPerPage(), $this->maxPerPage);
  }
}