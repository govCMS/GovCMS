<?php

namespace govCMS\Core;

/**
 * Class IniEncoder
 *
 * @package govCMS\Core
 */
class IniEncoder {

  /**
   * Serializes an array to legacy make format.
   *
   * @param array $input
   *   The data to serialize.
   *
   * @return string
   *   The serialized data.
   */
  public function encode(array $input) {
    return implode("\n", $this->doEncode($input));
  }

  /**
   * Recursively serializes data to legacy make format.
   *
   * @param array $input
   *   The data to serialize.
   * @param array $keys
   *   The current key path.
   *
   * @return string[]
   *   The serialized data as a flat array of lines.
   */
  protected function doEncode(array $input, array $keys = []) {
    $output = [];

    foreach ($input as $key => $value) {
      $keys[] = $key;

      if (is_array($value)) {
        if ($this->isAssociative($value)) {
          $output = array_merge($output, $this->doEncode($value, $keys));
        }
        else {
          foreach ($value as $j) {
            $output[] = $this->keysToString($keys) . '[] = ' . $j;
          }
        }
      }
      else {
        $output[] = $this->keysToString($keys) . ' = ' . $value;
      }

      array_pop($keys);
    }

    return $output;
  }

  /**
   * Transforms an key path to a string.
   *
   * @param string[] $keys
   *   The key path.
   *
   * @return string
   *   The flattened key path.
   */
  protected function keysToString(array $keys) {
    $head = array_shift($keys);
    if ($keys) {
      return $head . '[' . implode('][', $keys) . ']';
    }
    else {
      return $head;
    }
  }

  /**
   * Tests if an array is associative.
   *
   * @param array $input
   *   The array to test.
   *
   * @return bool
   *   Whether or not the array has non-numeric keys.
   */
  protected function isAssociative(array $input) {
    $keys = implode('', array_keys($input));

    return !is_numeric($keys);
  }

  /**
   * Parses data in Drupal's .info format.
   *
   * @see https://api.drupal.org/api/drupal/includes!common.inc/function/drupal_parse_info_format/7.x
   *
   * @param string $data
   *   A string to parse.
   *
   * @return array
   *   The parsed data.
   */
  public function parse($data) {
    $info = [];

    if (preg_match_all('
      @^\s*                           # Start at the beginning of a line, ignoring leading whitespace
      ((?:
        [^=;\[\]]|                    # Key names cannot contain equal signs, semi-colons or square brackets,
        \[[^\[\]]*\]                  # unless they are balanced and not nested
      )+?)
      \s*=\s*                         # Key/value pairs are separated by equal signs (ignoring white-space)
      (?:
        ("(?:[^"]|(?<=\\\\)")*")|     # Double-quoted string, which may contain slash-escaped quotes/slashes
        (\'(?:[^\']|(?<=\\\\)\')*\')| # Single-quoted string, which may contain slash-escaped quotes/slashes
        ([^\r\n]*?)                   # Non-quoted string
      )\s*$                           # Stop at the next end of a line, ignoring trailing whitespace
      @msx', $data, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        // Fetch the key and value string.
        $i = 0;
        foreach (['key', 'value1', 'value2', 'value3'] as $var) {
          $$var = isset($match[++$i]) ? $match[$i] : '';
        }

        if (isset($key) && isset($value1) && isset($value2) && isset($value3)) {
          $value = stripslashes(substr($value1, 1, -1)) . stripslashes(substr($value2, 1, -1)) . $value3;

          // Parse array syntax.
          $keys = preg_split('/\]?\[/', rtrim($key, ']'));
          $last = array_pop($keys);
          $parent = &$info;

          // Create nested arrays.
          foreach ($keys as $key) {
            if ($key == '') {
              $key = count($parent);
            }
            if (!isset($parent[$key]) || !is_array($parent[$key])) {
              $parent[$key] = [];
            }
            $parent = &$parent[$key];
          }

          // Handle PHP constants.
          if (preg_match('/^\w+$/i', $value) && defined($value)) {
            $value = constant($value);
          }

          // Insert actual value.
          if ($last == '') {
            $last = count($parent);
          }
          $parent[$last] = $value;
        }
      }
    }

    return $info;
  }

}
