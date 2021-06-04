<?php

declare(strict_types=1);

namespace Light;

/**
 * Class ErrorController
 * @package Light
 */
class ErrorController extends Controller
{
  /**
   * @var \Exception
   */
  private $_exception = null;

  /**
   * @var bool
   */
  private $_exceptionEnabled = true;

  /**
   * @return bool
   */
  public function isExceptionEnabled(): bool
  {
    return $this->_exceptionEnabled;
  }

  /**
   * @param bool $exceptionEnabled
   */
  public function setExceptionEnabled(bool $exceptionEnabled): void
  {
    $this->_exceptionEnabled = $exceptionEnabled;
  }

  /**
   * Setup status code and message
   */
  public function init()
  {
    parent::init();

    $this->getResponse()->setStatusCode(
      $this->getException()->getCode()
    );

    $this->getResponse()->setStatusMessage(
      $this->getException()->getMessage()
    );
  }

  /**
   * @return \Exception|Exception
   */
  public function getException()
  {
    return $this->_exception;
  }

  /**
   * @param \Exception $exception
   */
  public function setException(\Exception $exception)
  {
    $this->_exception = $exception;
  }
}
