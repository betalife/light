<?php

declare(strict_types=1);

namespace Light;

use Iterator;
use Light\Model\ModelInterface;

/**
 * Class Map
 * @package Light
 *
 * @method static array|Map execute(array|ModelInterface $data, string|array $context = null, array $userData = [], array $include = [])
 */
class Map implements Map\MapInterface, Iterator
{
  /**
   * @var array
   */
  public $_commonData = [];

  /**
   * @var array|object|Model
   */
  private $_data = [];

  /**
   * @var array
   */
  private $_userData = [];

  /**
   * @var int
   */
  private $_index = 0;

  /**
   * @var string
   */
  private $_context = null;

  /**
   * @var array
   */
  private $_include = [];

  /**
   * @param string $name
   * @param array $arguments
   *
   * @return array|Map
   *
   * @throws Exception
   * @throws Map\Exception\MapContextWasNotFound
   */
  public static function __callStatic($name, $arguments)
  {
    if ($name == 'execute') {

      $data = $arguments[0];

      if (!isset($arguments[1])) {
        $arguments[1] = 'common';
      }

      if (is_array($arguments[1])) {
        return self::executeArray($arguments[0], $arguments[1], $arguments[2] ?? [], $arguments[3] ?? []);
      }

      return self::executeMap($arguments[0], $arguments[1] ?? [], $arguments[2] ?? [], $arguments[3] ?? []);
    }

    throw new Exception([], 'Method ' . $name . ' not implemented', 500);
  }

  /**
   * @param array|Model\ModelInterface $data
   * @param array $context
   * @param array $userData
   * @param array $include
   *
   * @return array
   */
  public static function executeArray($data, array $context = [], array $userData = [], array $include = [])
  {
    $map = new self();

    $map->setData($data);
    $map->setContext('common');
    $map->setUserData($userData);

    $map->_commonData = $context;

    return $map->toArray();
  }

  /**
   * @return array
   */
  public function toArray(): array
  {
    if ($this->_isSingleData()) {
      return $this->current();
    }

    $arrayData = [];

    foreach ($this as $dataRow) {
      $arrayData[] = $dataRow;
    }

    return $arrayData;
  }

  /**
   * @return array|object
   */
  public function current()
  {
    $contextMap = $this->_getContextMap($this->getContext());

    $transformedDataRow = [];

    foreach ($contextMap as $name => $value) {

      if (count($this->getInclude()) && !in_array($name, $this->getInclude())) {
        continue;
      }

      if ($this->_isSingleData()) {
        $transformedDataRow[$name] = $this->_transform($this->getData(), $value);
      } else {
        $transformedDataRow[$name] = $this->_transform($this->_getDataRow($this->_index), $value);
      }
    }

    return $transformedDataRow;
  }

  /**
   * @param string $context
   * @return array
   */
  private function _getContextMap(string $context): array
  {
    return call_user_func_array([$this, $context], []);
  }

  /**
   * @return string
   */
  public function getContext(): string
  {
    return $this->_context;
  }

  /**
   * @param string $context
   */
  public function setContext(string $context)
  {
    $this->_context = $context;
  }

  /**
   * @return array
   */
  public function getInclude(): array
  {
    return $this->_include;
  }

  /**
   * @param array $include
   */
  public function setInclude(array $include): void
  {
    $this->_include = $include;
  }

  /**
   * @return bool
   */
  private function _isSingleData(): bool
  {
    $data = $this->getData();

    if (is_object($data) && $data instanceof Model\Driver\CursorAbstract) {
      return false;
    }

    if (is_array($data) && isset($data[0])) {
      return false;
    }

    return true;
  }

  /**
   * @return array|Model|object
   */
  public function getData()
  {
    return $this->_data;
  }

  /**
   * @param array|Model|object $data
   */
  public function setData($data)
  {
    $this->_data = $data;
  }

  /**
   * @param $dataRow
   * @param string|callable $name
   *
   * @return mixed
   */
  private function _transform($dataRow, $value)
  {
    if (is_callable($value)) {
      return $value($dataRow);
    }

    $getter = [$this, 'get' . ucfirst($value)];

    if (is_callable($getter)) {
      return call_user_func_array($getter, ['data' => $dataRow]);
    }

    if (is_array($dataRow)) {
      return isset($dataRow[$value]) ? $dataRow[$value] : null;
    }

    if (is_object($dataRow)) {
      return isset($dataRow->$value) ? $dataRow->$value : null;
    }

    return null;
  }

  /**
   * @param int $index
   *
   * @return array|object|Model|null
   */
  private function _getDataRow(int $index)
  {
    foreach ($this->getData() as $dataRowIndex => $dataRow) {
      if ($index == $dataRowIndex) {
        return $dataRow;
      }
    }

    return null;
  }

  /**
   * @param array|Model\ModelInterface $data
   * @param string $context
   * @param array $userData
   * @param array $include
   *
   * @return Map
   * @throws Map\Exception\MapContextWasNotFound
   */
  public static function executeMap(
    $data,
    string $context = 'common',
    array $userData = [],
    array $include = []
  ): Map
  {
    $mapClassName = static::class;

    /** @var Map $map */
    $map = new $mapClassName();
    $method = [$map, $context];

    if (is_callable($method)) {

      $map->setData($data);
      $map->setUserData($userData);
      $map->setContext($context);
      $map->setInclude($include);

      return $map;
    }

    throw new Map\Exception\MapContextWasNotFound($mapClassName, $context);
  }

  /**
   * @return array
   */
  public function getUserData(): array
  {
    return $this->_userData;
  }

  /**
   * @param array $userData
   */
  public function setUserData(array $userData = [])
  {
    $this->_userData = $userData;
  }

  /**
   * return null
   */
  public function next()
  {
    $this->_index++;
  }

  /**
   * @return int|null
   */
  public function key()
  {
    if ($this->_isSetDataRow($this->_index)) {
      return $this->_index;
    }

    return null;
  }

  /**
   * @param int $index
   * @return bool
   */
  private function _isSetDataRow(int $index): bool
  {
    foreach ($this->getData() as $dataRowIndex => $dataRow) {
      if ($index == $dataRowIndex) {
        return true;
      }
    }

    return false;
  }

  /**
   * @return bool
   */
  public function valid()
  {
    return $this->_isSetDataRow($this->_index);
  }

  /**
   * Move cursor to the begin
   * return null;
   */
  public function rewind()
  {
    $this->_index = 0;
  }

  /**
   * @return array|Model|null|object
   */
  public function getRow()
  {
    if ($this->_isSingleData()) {
      return $this->getData();
    }

    return $this->_getDataRow($this->_index);
  }

  /**
   * @return array
   */
  public function common(): array
  {
    return $this->_commonData;
  }
}
