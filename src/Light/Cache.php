<?php

declare(strict_types=1);

namespace Light;

/**
 * Class Cache
 * @package Light
 */
class Cache
{
  /**
   * @param array $title
   * @return array|mixed|null
   */
  public static function get(array $title)
  {
    if (!(Front::getInstance()->getConfig()['light']['cache']['enabled'] ?? false)) {
      return null;
    }

    $cache = \Light\Cache\Model::one([
      'title' => json_encode($title)
    ]);

    if (!$cache) {
      return null;
    }

    if ($cache->expire < time()) {
      $cache->remove();
    }

    return $cache->data;
  }

  /**
   * @param array $title
   * @param array $data
   */
  public static function set(array $title, array $data)
  {
    $cache = new \Light\Cache\Model();
    $cache->populate([
      'expire' => time() + Front::getInstance()->getConfig()['light']['cache']['lifetime'] ?? 10000,
      'title' => json_encode($title),
      'data' => $data
    ]);
    $cache->save();
  }

  /**
   * @return int
   */
  public static function clear()
  {
    return Cache\Model::remove();
  }

  /**
   * @param array $title
   * @param callable $callable
   *
   * @return array|mixed|null
   */
  public static function profile(array $title, callable $callable)
  {
    if ($data = self::get($title)) {
      return $data;
    }

    $data = $callable();

    self::set($title, $data);

    return $data;
  }
}