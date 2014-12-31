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
