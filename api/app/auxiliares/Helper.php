<?php

namespace App\auxiliares;

use App\Models\Config;

class Helper
{
    public static function getConfig(string $id, $default)
    {
      $value = $default;
      $sessionconfig = session('config');
      if (!$sessionconfig) $sessionconfig = [];
      $found_key = array_search($id, array_column($sessionconfig, 'id'));
      if ($found_key === false) {
        $config = Config::find($id);
        if ($config) $value = $config->asValue($default);
        $sessionconfig[] = [
          'id'      => $id,
          'value'   => $value
        ];
        session(['config' => $sessionconfig]);
        return $value;
      } else {
        return $sessionconfig[$found_key]['value'];
      }
    }
}
