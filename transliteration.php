<?php

/**
 * @file
 * Transliteration processing functions.
 */

/**
 * Transliterates UTF-8 encoded text to US-ASCII.
 *
 * Based on Mediawiki's UtfNormal::quickIsNFCVerify().
 * Based on Drupal 7 transliteration module.
 *
 * @param $string
 *   UTF-8 encoded text input.
 * @param $unknown
 *   Replacement string for characters that do not have a suitable ASCII
 *   equivalent.
 * @param $source_langcode
 *   Optional ISO 639 language code that denotes the language of the input and
 *   is used to apply language-specific variations. If the source language is
 *   not known at the time of transliteration, it is recommended to set this
 *   argument to the site default language to produce consistent results.
 *   Otherwise the current display language will be used.
 * @return
 *   Transliterated text.
 */
function _transliteration_process($string, $unknown = '?', $source_langcode = NULL) {
  // ASCII is always valid NFC! If we're only ever given plain ASCII, we can
  // avoid the overhead of initializing the decomposition tables by skipping
  // out early.
  if (!preg_match('/[\x80-\xff]/', $string)) {
    return $string;
  }

  static $tail_bytes;

  if (!isset($tail_bytes)) {
    // Each UTF-8 head byte is followed by a certain number of tail bytes.
    $tail_bytes = array();
    for ($n = 0; $n < 256; $n++) {
      if ($n < 0xc0) {
        $remaining = 0;
      }
      elseif ($n < 0xe0) {
        $remaining = 1;
      }
      elseif ($n < 0xf0) {
        $remaining = 2;
      }
      elseif ($n < 0xf8) {
        $remaining = 3;
      }
      elseif ($n < 0xfc) {
        $remaining = 4;
      }
      elseif ($n < 0xfe) {
        $remaining = 5;
      }
      else {
        $remaining = 0;
      }
      $tail_bytes[chr($n)] = $remaining;
    }
  }

  // Chop the text into pure-ASCII and non-ASCII areas; large ASCII parts can
  // be handled much more quickly. Don't chop up Unicode areas for punctuation,
  // though, that wastes energy.
  preg_match_all('/[\x00-\x7f]+|[\x80-\xff][\x00-\x40\x5b-\x5f\x7b-\xff]*/', $string, $matches);

  $result = '';
  foreach ($matches[0] as $str) {
    if ($str[0] < "\x80") {
      // ASCII chunk: guaranteed to be valid UTF-8 and in normal form C, so
      // skip over it.
      $result .= $str;
      continue;
    }

    // We'll have to examine the chunk byte by byte to ensure that it consists
    // of valid UTF-8 sequences, and to see if any of them might not be
    // normalized.
    //
    // Since PHP is not the fastest language on earth, some of this code is a
    // little ugly with inner loop optimizations.

    $head = '';
    $chunk = strlen($str);
    // Counting down is faster. I'm *so* sorry.
    $len = $chunk + 1;

    for ($i = -1; --$len; ) {
      $c = $str[++$i];
      if ($remaining = $tail_bytes[$c]) {
        // UTF-8 head byte!
        $sequence = $head = $c;
        do {
          // Look for the defined number of tail bytes...
          if (--$len && ($c = $str[++$i]) >= "\x80" && $c < "\xc0") {
            // Legal tail bytes are nice.
            $sequence .= $c;
          }
          else {
            if ($len == 0) {
              // Premature end of string! Drop a replacement character into
              // output to represent the invalid UTF-8 sequence.
              $result .= $unknown;
              break 2;
            }
            else {
              // Illegal tail byte; abandon the sequence.
              $result .= $unknown;
              // Back up and reprocess this byte; it may itself be a legal
              // ASCII or UTF-8 sequence head.
              --$i;
              ++$len;
              continue 2;
            }
          }
        } while (--$remaining);

        $n = ord($head);
        if ($n <= 0xdf) {
          $ord = ($n - 192) * 64 + (ord($sequence[1]) - 128);
        }
        elseif ($n <= 0xef) {
          $ord = ($n - 224) * 4096 + (ord($sequence[1]) - 128) * 64 + (ord($sequence[2]) - 128);
        }
        elseif ($n <= 0xf7) {
          $ord = ($n - 240) * 262144 + (ord($sequence[1]) - 128) * 4096 + (ord($sequence[2]) - 128) * 64 + (ord($sequence[3]) - 128);
        }
        elseif ($n <= 0xfb) {
          $ord = ($n - 248) * 16777216 + (ord($sequence[1]) - 128) * 262144 + (ord($sequence[2]) - 128) * 4096 + (ord($sequence[3]) - 128) * 64 + (ord($sequence[4]) - 128);
        }
        elseif ($n <= 0xfd) {
          $ord = ($n - 252) * 1073741824 + (ord($sequence[1]) - 128) * 16777216 + (ord($sequence[2]) - 128) * 262144 + (ord($sequence[3]) - 128) * 4096 + (ord($sequence[4]) - 128) * 64 + (ord($sequence[5]) - 128);
        }
        $result .= _transliteration_replace($ord, $unknown, $source_langcode);
        $head = '';
      }
      elseif ($c < "\x80") {
        // ASCII byte.
        $result .= $c;
        $head = '';
      }
      elseif ($c < "\xc0") {
        // Illegal tail bytes.
        if ($head == '') {
          $result .= $unknown;
        }
      }
      else {
        // Miscellaneous freaks.
        $result .= $unknown;
        $head = '';
      }
    }
  }
  return $result;
}

/**
 * Replaces a Unicode character using the transliteration database.
 *
 * @param $ord
 *   An ordinal Unicode character code.
 * @param $unknown
 *   Replacement string for characters that do not have a suitable ASCII
 *   equivalent.
 * @param $langcode
 *   Optional ISO 639 language code that denotes the language of the input and
 *   is used to apply language-specific variations.  Defaults to the current
 *   display language.
 * @return
 *   ASCII replacement character.
 */
function _transliteration_replace($ord, $unknown = '?', $langcode = NULL) {
  static $map = array();

  //GL: set language later
  /*
  if (!isset($langcode)) {
    global $language;
    $langcode = $language->language;
  }
  */

  $bank = $ord >> 8;

  if (!isset($map[$bank][$langcode])) {
    $file = './resources/transliteration-data/' . sprintf('x%02x', $bank) . '.php';  
    if (file_exists($file)) {
      include $file;
      if ($langcode != 'en' && isset($variant[$langcode])) {
        // Merge in language specific mappings.
        $map[$bank][$langcode] = $variant[$langcode] + $base;
      }
      else {
        $map[$bank][$langcode] = $base;
      }
    }
    else {
      $map[$bank][$langcode] = array();
    }
  }

  $ord = $ord & 255;

  return isset($map[$bank][$langcode][$ord]) ? $map[$bank][$langcode][$ord] : $unknown;
}
