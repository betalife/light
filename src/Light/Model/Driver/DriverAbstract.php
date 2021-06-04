<?php

declare(strict_types=1);

namespace Light\Model\Driver;

use Light\Model;

/**
 * Interface DriverInterface
 * @package Light\Driver
 */
abstract class DriverAbstract
{
  /**
   * @var Model
   */
  private $_model = null;

  /**
   * @var array
   */
  private $_config = null;

  /**
   * Driver constructor.
   * @param array $config
   */
  public function __construct(array $config = [])
  {
    $this->_config = $config;
  }

  /**
   * @return array
   */
  public function getConfig()
  {
    return $this->_config;
  }

  /**
   * @param array|string|null $cond
   * @param array|string|null $sort
   *
   * @return Model
   */
  public function fetchObject($cond = null, array $sort = null): Model
  {
    $model = static::fetchOne($cond, $sort);

    if ($model) {
      return $model;
    }

    return $this->getModel();
  }

  /**
   * @param array|string|null $cond
   * @param array|string|null $sort
   *
   * @return mixed|null
   */
  abstract public function fetchOne($cond = null, $sort = null);

  /**
   * @return Model
   */
  public function getModel(): Model
  {
    return $this->_model;
  }

  /**
   * @param Model $model
   */
  public function setModel(Model $model)
  {
    $this->_model = $model;
  }

  /**
   * Save object
   *
   * @return mixed
   */
  abstract public function save();

  /**
   * @param array|string|null $cond
   * @param int|null $limit
   *
   * @return int
   */
  abstract public function remove($cond = null, int $limit = null): int;

  /**
   * @param array|string|null $cond
   * @param array|string|null $sort
   *
   * @param int|null $count
   * @param int|null $offset
   *
   * @return CursorAbstract
   */
  abstract public function fetchAll($cond = null, $sort = null, int $count = null, int $offset = null);

  /**
   * @param array|string|null $cond
   * @return int
   */
  abstract public function count($cond = null): int;

  /**
   * @param array|null $data
   *
   * @return int
   */
  abstract public function batchInsert(array $data = null): int;
}