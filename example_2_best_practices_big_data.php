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

  @ob_end_clean(); // remove all buffers, including the *first* default one.

  mb_internal_encoding("ISO-8859-1");
  mb_http_input("ISO-8859-1");
  mb_http_output("ISO-8859-1");
  mb_regex_encoding("ISO-8859-1");
  setlocale(LC_ALL, "en_US.ISO-8859-1");

  include_once('functions.php');

  $content = @file_get_contents('hosts.txt');
  $compressed = @compress($content);

  date_default_timezone_set("Asia/Jerusalem");
  header("Accept-Ranges: bytes");
  header("Content-Encoding: gzip");

  header('Content-Type: text/plain; charset=ISO-8859-1');
  header("Content-Length: " . $compressed['compressed']['length']);

  header("X-Content-Length-Original: " . human_readable_bytes_size($compressed['original']['length']));
  header("X-Content-Compression-Ratio: " . round($compressed['ratio']['percent'], 3) . "%");

  echo $compressed['compressed']['string'];
