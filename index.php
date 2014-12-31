<?php


  /*           _                    _
   *          | |                  | |
   *     __ _ | |__    ___   _   _ | |_
   *   / _` | | '_ \  / _ \ | | | || __|
   *  | (_| | | |_) || (_) || |_| || |_
   *   \__,_| |_.__/  \___/  \__,_| \__|
   */


  /**
   * Avoiding 'ZLIB' and 'ob_gzhandler' buffer callback, and run lower'ish-level gzip compression,
   * complete with everything you'll ever need including ratio headers (so your data will stay intact, but you can
   * still see how much you've saved..)
   *
   *        this is a solution designed from scratch to handle the 'Content-Length' 'Accept-Ranges: bytes' headers,
   *        that workarounds a very pesky bug in PHP 5.3-5.6 engine, where buffers reports incorrect (or 0) length.
   *
   *        there are no overheads, since buffers are essentially just callbacks for methods, executed when content
   *        need to be flushed. If you want to, you may implement a wrap around this method too to be used as callback
   *        for the ob_start command, but why should you ;)
   *
   * @author  eladkarako@gmail.com (Elad Karako)
   * @link    http://icompile.eladkarako.com
   * @licence GPLV2.0
   */


  /*     __                     _    _
   *   / _|                    | |  (_)
   *  | |_  _   _  _ __    ___ | |_  _   ___   _ __   ___
   *  |  _|| | | || '_ \  / __|| __|| | / _ \ | '_ \ / __|
   *  | |  | |_| || | | || (__ | |_ | || (_) || | | |\__ \
   *  |_|   \__,_||_| |_| \___| \__||_| \___/ |_| |_||___/  *just one..
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


  /*                    _
   *                   (_)
   *  _ __ ___    __ _  _  _ __
   * | '_ ` _ \  / _` || || '_ \
   * | | | | | || (_| || || | | |
   * |_| |_| |_| \__,_||_||_| |_|
   */


  @ob_end_clean(); // remove all buffers, including the *first* default one.

  //------------------------------------------------------------------------
  //generate content here (* you replace this with you own content *)
  $content = str_repeat('a', 4096); //generate 4KB size content.
  //------------------------------------------------------------------------

  $compressed = compress($content);

  header("Accept-Ranges: bytes");
  header("Content-Encoding: gzip");
  header("Content-Length: " . $compressed['compressed']['length']);


  echo $compressed['compressed']['string'];


  die(0);
?>
