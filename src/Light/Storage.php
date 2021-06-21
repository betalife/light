<?php

declare(strict_types=1);

namespace Light;

/**
 * Class Storage
 * @package Light
 */
class Storage
{
  /**
   * @var array
   */
  private static $_settings = null;

  /**
   * @return array
   */
  public static function getSettings(): ?array
  {
    return self::$_settings;
  }

  /**
   * @param array $settings
   */
  public static function setSettings(?array $settings): void
  {
    self::$_settings = $settings;
  }

  /**
   * @param string $path
   * @param string $folder
   * @param array $files
   *
   * @return array
   * @throws \Exception
   */
  public static function upload(string $path, string $folder, array $files): array
  {
    if (!self::$_settings) {
      throw new \Exception('Storage settings is not defined');
    }

    try {

      $data = [
        'files' => [],
        'path' => $path,
        'folder' => $folder,
      ];

      foreach ($files as $file) {
        $data['files'][] = [
          'content' => base64_encode(file_get_contents($file)),
          'mime' => mime_content_type($file)
        ];
      }

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, self::$_settings['url'] . 'api/uploadFileBase64');
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

      $result = curl_exec($ch);
      curl_close($ch);

      return json_decode($result, true);

    } catch (\Exception $e) {
      throw new \Exception('Cannot upload files');
    }
  }
}
