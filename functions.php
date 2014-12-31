<?php
  /*     __                     _    _
   *   / _|                    | |  (_)
   *  | |_  _   _  _ __    ___ | |_  _   ___   _ __   ___
   *  |  _|| | | || '_ \  / __|| __|| | / _ \ | '_ \ / __|
   *  | |  | |_| || | | || (__ | |_ | || (_) || | | |\__ \
   *  |_|   \__,_||_| |_| \___| \__||_| \___/ |_| |_||___/
   */


  /**
   * Compress and return an associative array of the original, compressed and delta-ration.
   *
   * @param $string
   *
   * @return array
   */
  function compress($string) {
    $prefix_headers =
      ''
      /* +---+---+---+---+---+---+---+---+---+---+
       * |ID1|ID2|CM |FLG|     MTIME     |XFL|OS |
       * +---+---+---+---+---+---+---+---+---+---+
       */

      //(ID1,ID2) - identification: this is gzip
      . "\x1f" . "\x8b"

      //(CM)      - compression method: this is deflate
      . "\x08"
      . "\x00"
      . "\x00\x00\x00\x00"; //unused flags (http://www.gzip.org/zlib/rfc-gzip.html#header-trailer)

    $length = mb_strlen($string);
    $checksum = crc32($string);

    $string_compressed = gzcompress($string); //compress
    $string_compressed = mb_substr($string_compressed, 0, $length - 4); //apparently fix something

    //prefix + { compressed } + suffix
    $string_compressed = $prefix_headers . $string_compressed; //add binary-string headers

    $string_compressed = $string_compressed . pack('V', $checksum); // add "Cyclic Redundancy Check value" of the original (uncompressed) string, as binary-string (CRC-32 - unsigned long, always 32 bit, little endian byte order).
    $string_compressed = $string_compressed . pack('V', $length);   // add length of the original (uncompressed) string, as binary-string, (unsigned long (always 32 bit, little endian byte order).
    $length_compressed = mb_strlen($string_compressed);


    return [
      'original'   => [
        'string' => $string,
        'length' => $length
      ],
      'compressed' => [
        'string' => $string_compressed,
        'length' => $length_compressed
      ],
      'ratio'      => [
        'percent' => ($length_compressed / $length) * 100,
        'delta'   => $length - $length_compressed
      ]
    ];
  }

  /**
   * human_readable_bytes_size
   *
   * @param int $bytes
   * @param int $decimals
   *
   * @return string
   */
  function human_readable_bytes_size($bytes, $decimals = 2) {
    $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $factor = (int)(floor((strlen($bytes) - 1) / 3));

    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[ $factor ];
  }

?>
