<?php
  /// copyleft 2012 meklu (public domain)
  // This tries to check whether a URL should be accessed
  // or not.
  // The latest version should be available at
  //   http://meklu.webege.com/code/rbt_prs.php.bz2
  // or alternatively at
  //   https://github.com/meklu/rbt_prs
  // Just pass the url (and user agent, if you'd like) to the function.
  // You can also pass your own robots.txt to it as $robots_txt and set
  // $redirects to something if you'd like to allow redirects. TRUE will
  // enable them and use 20 as the value, whereas FALSE disables this. 1 or
  // less means that none are followed.
  // If an argument is NULL, its default value will be used.
  define("RBT_PRS_VER_MAJOR", "1");
  define("RBT_PRS_VER_MINOR", "1");
  define("RBT_PRS_VER_PATCH", "2");
  define("RBT_PRS_BRANCH", "master");
  define("RBT_PRS_VER", RBT_PRS_VER_MAJOR . "." . RBT_PRS_VER_MINOR . "." .
	  RBT_PRS_VER_PATCH . "-" . RBT_PRS_BRANCH);
  define("RBT_PRS_UA", "rbt_prs/" . RBT_PRS_VER .
	 " (https://github.com/meklu/rbt_prs)");
  function isUrlBotSafe($url, $your_useragent = RBT_PRS_UA, $robots_txt = NULL,
			$redirects = FALSE, $debug = FALSE) {
    if($your_useragent === NULL) $your_useragent = RBT_PRS_UA;
    if($debug === NULL) $debug = FALSE;
    if($redirects === NULL) $redirects = FALSE;

    if($redirects === TRUE) $redirects = 20;
    if($redirects !== FALSE && is_int($redirects) === TRUE) {
      $redirectarray = array('http' => array('method' => 'GET',
					     'max_redirects' => $redirects)
			    );
      $redirectcontext = stream_context_create($redirectarray);
    }

    if($debug === TRUE) {
      echo "Argument initialisation finished.\n";
      error_reporting(E_ALL);
      ini_set('display_errors', true);
      ini_set('html_errors', false);
      echo "PHP version: " . phpversion() . "\n";
      echo "rbt_prs version: " . RBT_PRS_VER . "\n";
    }
    // storing the current ua
    $original_ua=ini_get("user_agent");
    // switching to the given ua
    ini_set("user_agent", $your_useragent);
    if($debug === TRUE)
      echo "User agent stored and switched.\n";
    // slicing up the given url
    $tmp=parse_url($url);
    // start re-assembling it
    $baseurl=$tmp["scheme"] . "://";
    if(isset($tmp["user"])) {
      $baseurl=$baseurl . $tmp["user"] . ":";
      if(isset($tmp["pass"])) {
	$basurl=$baseurl . $tmp["pass"];
      }
      $baseurl=$baseurl . "@";
    }
    $baseurl=$baseurl . $tmp["host"];
    if(isset($tmp["port"])) {
      $baseurl=$baseurl . ":" . $tmp["port"];
    }
    $baseurl=$baseurl . "/";
    if(isset($tmp["path"])) {
      $checkedpath=$tmp["path"];
    } else {
      $checkedpath="/";
    }
    if(isset($tmp["query"])) {
      $checkedpath=$checkedpath . "?" . $tmp["query"];
    }
    if($debug === TRUE)
      echo "URL sliced and re-assembled.\n";
    // re-assembling the url is finished
    // do a bit of magic on the checked path
    if(strlen($checkedpath) > 1) {
      if(substr_count($checkedpath, '?') == 0) {
	if(preg_match("#\w$#", $checkedpath))
	  $checkedpath=$checkedpath . "/";
      } elseif(substr_count($checkedpath, "/?") > 0) {
	$checkedpath=str_replace("/?", "/index.stuff?", $checkedpath);
      }
    }
    unset($tmp);
    if($debug === TRUE)
      echo "Checked path magic done.\n";
    // checking whether robots.txt can be accessed or not if the user hasn't
    // supplied one.
    if($robots_txt === NULL) {
      if($redirects !== FALSE && is_int($redirects) === TRUE) {
	if($debug === TRUE && $redirects <= 1)
	  echo "Warning! Setting \$redirects to 1 or less may break things!\n";
	if($debug === TRUE) {
	  $fhandle=fopen($baseurl . "robots.txt", "rb", FALSE,
			 $redirectcontext);
	} else {
	  $fhandle=@fopen($baseurl . "robots.txt", "rb", FALSE,
			  $redirectcontext);
	}
      } else {
	if($debug === TRUE) {
	  $fhandle=fopen($baseurl . "robots.txt", "rb");
	} else {
	  $fhandle=@fopen($baseurl . "robots.txt", "rb");
	}
      }
      if($fhandle === FALSE) {
	unset($fhandle);
	ini_set("user_agent", $original_ua);
	return TRUE;
      } else {
	if($debug === TRUE)
	  echo "Obtained file handle.\n";
	// we were able to download something!
	$raw=stream_get_contents($fhandle);
	fclose($fhandle);
	unset($fhandle);
	// check if we ran into an html (error) page.
	// we're looking for the closing tag because <html foo="bar">
	if(preg_match("#</html>#i", $raw) > 0) {
	  if($debug === TRUE) {
	    echo "Ran into HTML, aborting and returning TRUE...\n";
	    var_dump($raw);
	  }
	  ini_set("user_agent", $original_ua);
	  return TRUE;
	}
      }
    } else {
      $raw=$robots_txt;
      unset($robots_txt);
    }
    if($debug === TRUE)
      echo "robots.txt loaded.\n";
    // so far so good!
    // fixing some newlines and removing comments on top of which we're
    // escaping a few characters
    // i.e. making all carriage returns newlines and removing duplicates
    //      and trimming the result
    $orig_raw=$raw;
    $raw=str_replace("\r", "\n", $raw);
    // remove the comments
    $raw=preg_replace(":(#).*:", "", $raw);
    // first the backslashes
    $raw=str_replace("\\", "\\\\", $raw);
    // then the rest
    $raw=str_replace(".", "\\.", $raw);
    $raw=str_replace("?", "\\?", $raw);
    // remove duplicate newlines
    $raw=preg_replace("#\n+#", "\n", $raw);
    // replace empty disallows with "Allow: /" since some people use that
    $raw=preg_replace("#^Disallow:(\h*)$#im", "Allow: /", $raw);
    // trim that
    $raw=trim($raw);
    if($debug === TRUE)
      echo "robots.txt touched up.\n";

    // explode the lines into an array
    $lines=explode("\n", $raw);
    // initialise our multi-dimensional rule array
    $rules=array("*" => array(
			  "/" => TRUE
			)
		);
    // set current user agent to NULL
    // this means that lines before the first declaration of a user agent will
    // be ignored
    $current_agent=NULL;
    // process the lines individually
    foreach($lines as &$line) {
      // explode our lines into two segments
      $rule=explode(":", $line, 2);
      // check if we had enough elements
      // this makes us silently ignore invalid entries
      if(count($rule) == 2) {
	$key=trim($rule[0]);
	$value=trim($rule[1]);
      } else {
	if($debug === TRUE)
	  echo "Less than two pieces of rule.\n\t\"" . $rule[0] . "\"\n";
	unset($rule);
	continue;
      }
      // is it a user agent?
      if(strcasecmp($key, "user-agent") == 0) {
	$current_agent=$value;
	if($debug === TRUE)
	  echo "User agent match.\n\t\"" . $value . "\"\n";
	unset($rule, $key, $value);
	continue;
      }
      // is it an allow?
      if(strcasecmp($key, "allow") == 0 && $current_agent !== NULL) {
      	if(strlen($value) > 1) {
	  if(substr_count($value, '?') == 0) {
	    if(preg_match("#\w$#", $value))
	      $value=$value . "/";
	  } else {
	    if(substr_count($value, "/\\?") > 0) {
	      $value=str_replace("/\\?", "/index\\.\w+\\?", $value);
	    }
	  }
	}
	$rules[$current_agent][$value]=TRUE;
	if($debug === TRUE)
	  echo "Allow match.\n\t\"" . $value . "\"\n";
	unset($rule, $key, $value);
	continue;
      }
      // is it a disallow?
      if(strcasecmp($key, "disallow") == 0 && $current_agent !== NULL) {
      	if(strlen($value) > 1) {
	  if(substr_count($value, '?') == 0) {
	    if(preg_match("#\w$#", $value))
	      $value=$value . "/";
	  } else {
	    if(substr_count($value, "/\\?") > 0) {
	      $value=str_replace("/\\?", "/index\\.\w+\\?", $value);
	    }
	  }
	}
	$rules[$current_agent][$value]=FALSE;
	if($debug === TRUE)
	  echo "Disallow match.\n\t\"" . $value . "\"\n";
	unset($rule, $key, $value);
	continue;
      }
      if($debug === TRUE)
	echo "No match.\n\t\"" . $key . ": " . $value . "\"\n";
      unset($rule, $key, $value);
    }
    unset($line);
    unset($current_agent);
    if($debug === TRUE)
      echo "Rules parsed.\n";
    // let's see if we have a match
    // $state is TRUE by default because why not
    $state=TRUE;
    // first checking universal rules
    if(isset($rules["*"])) {
      if(isset($rules["*"]["/"]))
	$state=$rules["*"]["/"];
      reset($rules["*"]);
      foreach($rules["*"] as $key => $value) {
	if(preg_match("#^" . $key . "#", $checkedpath) > 0) {
	  $state=$value;
	}
      }
    }
    if($debug === TRUE)
      echo "Universal rules checked.\n";
    // checking rules specific to your user agent
    // exploding it into product tags while stripping off all extra info inside
    // brackets
    $ua_array=explode(" ", trim(preg_replace("# +#", " ",
		      preg_replace("#\(.*?\)#", "", $your_useragent))));
    // reversing the array since the first mentioned product tag should be the
    // most specific (Section 14.43 of RFC 2616)
    $ua_array=array_reverse($ua_array);
    foreach($ua_array as $useragent) {
      $tmp=preg_replace("#/(.*)#", "", $useragent);
      if(isset($rules[$tmp])) {
	if(isset($rules[$tmp]["/"]))
	  $state=$rules[$tmp]["/"];
	reset($rules[$tmp]);
	foreach($rules[$tmp] as $key => $value) {
	  if(preg_match("#^" . $key . "#", $checkedpath) > 0) {
	    $state=$value;
	  }
	}
      }
      if($debug === TRUE)
	echo "Specific rules for user agent " . $tmp . " checked.\n";
      unset($tmp);
    }
    if($debug === TRUE) {
      echo "Var dumps...\n";
      echo "\$checkedpath:\n";
      var_dump($checkedpath);
      echo "\$baseurl:\n";
      var_dump($baseurl);
      echo "\$redirects:\n";
      var_dump($redirects);
      echo "\$raw:\n";
      var_dump($raw);
      echo "\$orig_raw:\n";
      var_dump($orig_raw);
      echo "\$rules:\n";
      var_dump($rules);
      echo "\$your_useragent:\n";
      var_dump($your_useragent);
      echo "\$ua_array:\n";
      var_dump($ua_array);
      echo "The URL is ";
      if($state === TRUE) {
	echo "safe.\n";
      } else {
	echo "unsafe.\n";
      }
    }
    ini_set("user_agent", $original_ua);
    return $state;
  }
