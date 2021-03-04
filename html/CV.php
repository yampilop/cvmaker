<?php

function val_url($url) {
	if (filter_var(trim($url), FILTER_VALIDATE_URL)) {
		return true;
	}
	return false;
}

function val_email($email) {
	if (filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
		return true;
	}
	return false;
}

function get_in_lang($param, $language) {
	if (isset($param->{$language})) {
		if (val_url($param->{$language})) {
			return "<a href='".$param->{$language}."'>".$param->{$language}."</a>";
		} else if (val_email($param->{$language})) {
			return "<a href='mailto:".$param->{$language}."'>".$param->{$language}."</a>";
		} else {
			return $param->{$language};
		}
	} else {
		if (val_url($param)) {
			return "<a href='".$param."'>".$param."</a>";
		} else if (val_email($param)) {
			return "<a href='mailto:".$param."'>".$param."</a>";
		} else {
			return $param;
		}
	}
}

function make_sortable($object) {
	$sortable = array();
	foreach($object as $node) {
		$sortable[] = $node;
	}
	
	return $sortable;
}

function parse_date($string) {
	$retval = DateTime::createFromFormat('Y#m#d|', $string);
	if (!$retval) {
		$retval = DateTime::createFromFormat('Y#m|', $string);
		if (!$retval) {
			$retval = DateTime::createFromFormat('Y|', $string);
			if (!$retval) {
				$retval = new DateTime();
			}
		}
	}
	
	return $retval;
}

function compare_end_date($a, $b) {
	$date_a = parse_date($a->end_date);
	$date_b = parse_date($b->end_date);
	if ($date_a == $date_b) {
		return 0;
	}
	
	return ($date_a < $date_b) ? 1 : -1;
}

$file = "data/data.xml";
if (file_exists($file)) {
	libxml_use_internal_errors(true);
	$xml = simplexml_load_file($file);
	
	if ($xml === false) {
		echo "Error\n";
		
		foreach(libxml_get_errors() as $error) {
			echo "\t", $error->message;
		}
	} else {
		$style = isset($_POST["style"]) ? $_POST["style"] : "Standard";
		$page_setting_selected = isset($_POST["page_setting"]) ? $_POST["page_setting"] : "A4_10mm";
		$filter_selected = isset($_POST["filter"]) ? $_POST["filter"] : "full";
		$language_selected = isset($_POST["language"]) ? $_POST["language"] : "spa";
		
		$sans_font = isset($_POST["sans_font"]) ? $_POST["sans_font"] : "'Verdana', 'Noto Sans', sans-serif";
		$serif_font = isset($_POST["serif_font"]) ? $_POST["serif_font"] : "'Georgia', 'Droid Serif', serif";
?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Droid+Serif|Noto+Sans" />
	<style>
		<?php 
			$styles = array("Standard", "Stylish");
			
			$page_setting = $xml->configuration->page_settings->$page_setting_selected;
			
			$width = $page_setting->width;
			$height = $page_setting->height;
			$margins = $page_setting->margins;
			$page_numbers = $page_setting->page_numbers;
			
			$title = get_in_lang($xml->header->title, $language_selected);
			$full_name = get_in_lang($xml->personal_data->parameters->full_name->value, $language_selected);
			
			echo "@page {
				size: $width $height;
				margin: $margins;
			}
			
			@media screen {
				.print {
					padding: $margins;
				}
			}
			
			.print {
				margin: auto;
				width: $width;
				background-color: #FFFFFF;
			}
			
			body {
				font-family: $sans_font;
			}
			
			p.title, p.name, h1, h2, h3 {
				font-family: $serif_font;
			}\n";
		?>
	</style>
	<title><?php echo 'CV_'.$language_selected.'_'.$filter_selected ?></title>
</head>
<body>
<div class="no-print">
	<form method="POST" action="CV.php">
		<select name='style'>
		<?php 
			foreach($styles as $name) {
				$selected = ($name == $style) ? " selected" : "";
				echo "<option value='$name' $selected>".$name."</option>\n";
			}
		?>
		</select>
		
		<select name='page_setting'>
		<?php 
			foreach($xml->configuration->page_settings->children() as $name => $page_settings) {
				$selected = ($name == $page_setting_selected) ? " selected" : "";
				echo "<option value='$name' $selected>".$page_settings->title."</option>\n";
			}
		?>
		</select>
		
		<select name='language'>
		<?php 
			foreach($xml->configuration->languages->children() as $name => $language) {
				$selected = ($name == $language_selected) ? " selected" : "";
				echo "<option value='$name' $selected>".$language."</option>\n";
			}
		?>
		</select>
		
		<select name='filter'>
		<?php 
			foreach($xml->configuration->filters->children() as $name => $filter) {
				$selected = ($name == $filter_selected) ? " selected" : "";
				echo "<option value='$name' $selected>".$filter."</option>\n";
			}
		?>
		</select>
		
		<select name='serif_font'>
			<option value="Georgia" <?php if ($serif_font == 'Georgia') echo "selected"; ?>>Georgia</option>
			<option value="Droid Serif" <?php if ($serif_font == 'Droid Serif') echo "selected"; ?>>Droid Serif</option>
			<option value="Palatino" <?php if ($serif_font == 'Palatino') echo "selected"; ?>>Palatino</option>
		</select>
		<select name='sans_font'>
			<option value="Verdana" <?php if ($sans_font == 'Verdana') echo "selected"; ?>>Verdana</option>
			<option value="Noto Sans" <?php if ($sans_font == 'Noto Sans') echo "selected"; ?>>Noto Sans</option>
			<option value="Helvetica" <?php if ($sans_font == 'Helvetica') echo "selected"; ?>>Helvetica</option>
		</select>
		
		<input type='submit' value='Apply' />
	</form>
</div>

<div class="print">
<?php
	if ($style == "Standard") {
		/*
		########################################################################
		# STANDARD #############################################################
		########################################################################
		*/
		echo "<p class='title'>$title</p>\n";
		echo "<p class='name'>$full_name</p>\n";
		
		// Personal data
		echo "<h1>".get_in_lang($xml->personal_data->title, $language_selected)."</h1>\n";
		
		echo "<table>\n";
		echo "  <tr>\n";
		echo "    <td align='left'>\n";
		echo "      <p>\n";
		foreach ($xml->personal_data->parameters->children() as $name => $parameter) {
			echo "        <b>".get_in_lang($parameter->title, $language_selected).":</b> ".get_in_lang($parameter->value, $language_selected)."<br>\n";
		}
		echo "      </p>\n";
		echo "    </td>\n";
		echo "    <td align='right' width='200px'>\n";
		echo "      <img src='data/pictures/".$xml->personal_data->photo."' width='200px' height='200px' />\n";
		echo "    </td>\n";
		echo "  </tr>\n";
		echo "</table>\n";
		
		echo "<table>\n";
		echo "  <tr>\n";
		echo "    <td align='left'>\n";
		// Headline
		echo "      <h1>".get_in_lang($xml->headline->title, $language_selected)."</h1>\n";
		foreach ($xml->headline->paragraphs->children() as $paragraph) {
			if (in_array($filter_selected, str_getcsv($paragraph['filters']))) {
				echo "      ".get_in_lang($paragraph, $language_selected)."<br>\n";
			}
		}
		echo "    </td>\n";
		echo "    <td width='20px'>\n";
		echo "    </td>\n";
		echo "    <td align='center' width='200px'>\n";
		// Languages
		echo "<h1 class='languages'>".get_in_lang($xml->languages->title, $language_selected)."</h1>\n";
		foreach ($xml->languages->languages->children() as $language) {
			echo "      <h2 class='language'>".get_in_lang($language->title, $language_selected)." (".get_in_lang($language->fluency, $language_selected).")</h2>\n";
			
			echo "      <table class='language'>\n";
			echo "        <tr>\n";
			echo "          <td class='language'><img src='pictures/listening.png' width='20px' height='20px' /> ".$language->levels->listening."</td>\n";
			echo "          <td class='language'><img src='pictures/speaking.png' width='20px' height='20px' /> ".$language->levels->speaking."</td>\n";
			echo "          <td class='language'><img src='pictures/reading.png' width='20px' height='20px' /> ".$language->levels->reading."</td>\n";
			echo "          <td class='language'><img src='pictures/writing.png' width='20px' height='20px' /> ".$language->levels->writing."</td>\n";
			echo "        </tr>\n";
			echo "      </table>\n";
		}
		echo "    </td>\n";
		echo "  </tr>\n";
		echo "</table>\n";
		
		// Work experience
		echo "<h1>".get_in_lang($xml->work_experience->title, $language_selected)."</h1>\n";
		foreach ($xml->work_experience->experiences->children() as $experience) {
			if (in_array($filter_selected, str_getcsv($experience['filters']))) {
				echo "<table>\n";
				echo "  <tr>\n";
				echo "    <td align='left'>\n";
				echo "      <h2>".get_in_lang($experience->enterprise, $language_selected)."</h2>\n";
				
				foreach ($experience->positions->children() as $position) {
					echo "      <h3>&bull; ".get_in_lang($position->title, $language_selected)."</h3>\n";
					
					echo "      <p class='position_info'>".$position->location." | ".$position->start_date." - ".get_in_lang($position->end_date, $language_selected)."</p>\n";
					
					echo "      <p class='p3b'>\n";
					foreach ($position->details->children() as $paragraph) {
						if (in_array($filter_selected, str_getcsv($paragraph['filters']))) {
							echo "        ".get_in_lang($paragraph, $language_selected)."<br>\n";
						}
					}
					echo "      </p>\n";
				}
				
				echo "    </td>\n";
				echo "    <td class='logo' align='center' width='90px'>\n";
				echo "      <img src='data/pictures/".$experience->logo."' width='80px' height='80px' />\n";
				echo "    </td>\n";
				echo "  </tr>\n";
				echo "</table>\n";
			}
		}
		
		// Education
		echo "<h1>".get_in_lang($xml->education->title, $language_selected)."</h1>\n";
		foreach ($xml->education->experiences->children() as $experience) {
			if (in_array($filter_selected, str_getcsv($experience['filters']))) {
				echo "<table>\n";
				echo "  <tr>\n";
				echo "    <td align='left'>\n";
				echo "      <h2>".get_in_lang($experience->institution, $language_selected)."</h2>\n";
				
				foreach ($experience->careers->children() as $career) {
					if (in_array($filter_selected, str_getcsv($career['filters']))) {
						echo "      <h3>&bull; ".get_in_lang($career->title, $language_selected)."</h3>\n";
						
						$location = in_array($filter_selected, str_getcsv($career->location['filters'])) ? $career->location." | " : "";
						$start_date = in_array($filter_selected, str_getcsv($career->start_date['filters'])) ? $career->start_date." - " : "";
						$end_date = in_array($filter_selected, str_getcsv($career->end_date['filters'])) ? get_in_lang($career->end_date, $language_selected) : "";
						
						echo "      <p class='position_info'>".$location.$start_date.$end_date."</p>\n";
						
						echo "      <p class='p3b'>\n";
						foreach ($career->details->children() as $paragraph) {
							if (in_array($filter_selected, str_getcsv($paragraph['filters']))) {
								echo "        ".get_in_lang($paragraph, $language_selected)."<br>\n";
							}
						}
						echo "      </p>\n";
					}
				}
				
				echo "    </td>\n";
				echo "    <td class='logo' align='center' width='90px'>\n";
				echo "      <img src='data/pictures/".$experience->logo."' width='80px' height='80px' />\n";
				echo "    </td>\n";
				echo "  </tr>\n";
				echo "</table>\n";
			}
		}
		
		// Courses
		echo "<h1>".get_in_lang($xml->courses->title, $language_selected)."</h1>\n";
		
		$courses = make_sortable($xml->courses->courses->children());
		usort($courses, 'compare_end_date');
		
		foreach ($courses as $course) {
			if (in_array($filter_selected, str_getcsv($course['filters']))) {
				$duration = (in_array($filter_selected, str_getcsv($course->duration['filters'])) && ($course->duration != "")) ? " | ".$course->duration."h" : "";
				echo "      <p class='p3'><b>".get_in_lang($course->title, $language_selected)."</b> | ".get_in_lang($course->institution, $language_selected)." <font color='#AAAAAA'>| ".get_in_lang($course->end_date, $language_selected).$duration."</font></p>\n";
			}
		}
		
		// Skills
		echo "<h1>".get_in_lang($xml->skills->title, $language_selected)."</h1>\n";
		foreach ($xml->skills->categories->children() as $category) {
			if (in_array($filter_selected, str_getcsv($category['filters']))) {
				echo "<h3>".get_in_lang($category->title, $language_selected)."</h3>\n";
				foreach ($category->skills->children() as $skill) {
					if (in_array($filter_selected, str_getcsv($skill['filters']))) {
						echo "<p class='p3'>".get_in_lang($skill->title, $language_selected)." <font color='#AAAAAA'>| ".get_in_lang($skill->description, $language_selected)."</font></p>\n";
					}
				}
			}
		}
		
		// Projects
		echo "<h1>".get_in_lang($xml->projects->title, $language_selected)."</h1>\n";
		
		$projects = make_sortable($xml->projects->projects->children());
		usort($projects, 'compare_end_date');
		
		foreach ($projects as $project) {
			if (in_array($filter_selected, str_getcsv($project['filters']))) {
				echo "<h3>".get_in_lang($project->title, $language_selected)."</h3>\n";
				echo "<p class='p3'><b>".get_in_lang($project->institution, $language_selected)."</b> <font color='#AAAAAA'>| ".$project->start_date." - ".get_in_lang($project->end_date, $language_selected)."</font></p>\n";
				echo "<p class='p3'>\n";
				foreach ($project->description->children() as $paragraph) {
					echo get_in_lang($paragraph, $language_selected)."<br>\n";
				}
				echo "</p>\n";
			}
		}
		
		// echo "<pre>"; print_r($filters); echo "</pre>";
		
	} else if ($style == "Stylish") {
		/*
		########################################################################
		# STYLISH ##############################################################
		########################################################################
		*/
		echo "<table>\n";
		echo "  <tr style='height: $height;'>\n";
		echo "    <td width='25%' style='background-color: #ff7800;'>\n";
		echo "      <table>\n";
		echo "        <tr>\n";
		echo "          <td style='padding: 15px;' align='center'>\n";
		echo "            <img src='data/pictures/".$xml->personal_data->photo."' class='clip-circle' width='80%' />\n";
		echo "          </td>\n";
		echo "        </tr>\n";
		echo "        <tr>\n";
		echo "          <td style='padding: 5px;' align='left'>\n";
		
		// Personal data
		echo "            <p class='title'>$full_name</p>\n";
		foreach ($xml->personal_data->parameters->children() as $name => $parameter) {
			$additional = '';
			if ($name == 'mobile_phone') {
				$telephoneparsed = $parameter->value;
				$telephoneparsed = str_replace('(', '', $telephoneparsed);
				$telephoneparsed = str_replace(')', '', $telephoneparsed);
				$telephoneparsed = str_replace('+', '', $telephoneparsed);
				$telephoneparsed = str_replace('-', '', $telephoneparsed);
				$telephoneparsed = str_replace(' ', '', $telephoneparsed);
				
				$additional = " <a href='https://api.whatsapp.com/send?phone=$telephoneparsed'><img src='pictures/whatsapp.png' style='vertical-align: middle;'/></a>";
			}
			
			echo "            <p class='small'><b>".get_in_lang($parameter->title, $language_selected).":</b> ".get_in_lang($parameter->value, $language_selected)."$additional</p>\n";
		}
		
		echo "          </td>\n";
		echo "        </tr>\n";
		echo "        <tr>\n";
		echo "          <td align='center'>\n";
		
		// Languages
		echo "            <h2 class='language'>".get_in_lang($xml->languages->title, $language_selected)."</h2>\n";
		foreach ($xml->languages->languages->children() as $language) {
			echo "            <h3 class='language'>".get_in_lang($language->title, $language_selected)." (".get_in_lang($language->fluency, $language_selected).")</h3>\n";
			
			echo "            <table class='language_stylish'>\n";
			echo "              <tr>\n";
			echo "                <td align='center' width='25%'><p class='small'><img src='pictures/listening.png' width='20px' height='20px' /> ".$language->levels->listening."</p></td>\n";
			echo "                <td align='center' width='25%'><p class='small'><img src='pictures/speaking.png' width='20px' height='20px' /> ".$language->levels->speaking."</p></td>\n";
			echo "                <td align='center' width='25%'><p class='small'><img src='pictures/reading.png' width='20px' height='20px' /> ".$language->levels->reading."</p></td>\n";
			echo "                <td align='center' width='25%'><p class='small'><img src='pictures/writing.png' width='20px' height='20px' /> ".$language->levels->writing."</p></td>\n";
			echo "              </tr>\n";
			echo "            </table>\n";
		}
		
		echo "          </td>\n";
		echo "        </tr>\n";
		echo "        <tr>\n";
		echo "          <td align='left' style='padding: 5px;'>\n";
		
		// Skills
		echo "            <h2 class='language'>".get_in_lang($xml->skills->title, $language_selected)."</h1>\n";
		foreach ($xml->skills->categories->children() as $category) {
			if (in_array($filter_selected, str_getcsv($category['filters']))) {
				echo "              <h3 class='stylish_black'>".get_in_lang($category->title, $language_selected)."</h3>\n";
				foreach ($category->skills->children() as $skill) {
					if (in_array($filter_selected, str_getcsv($skill['filters']))) {
						echo "              <p class='small' style='text-align: center;'>".get_in_lang($skill->title, $language_selected)."</p>\n";
					}
				}
			}
		}
		
		echo "          </td>\n";
		echo "        </tr>\n";
		echo "      </table>\n";
		echo "    </td>\n";
		echo "    <td width='75%' style='background-color: #ffffff;'>\n";
		
		echo "      <table>\n";
		echo "        <tr>\n";
		echo "          <td style='background-color: #c0bfbc; padding: 20px;' align='left'>\n";
		
		// Headline
		foreach ($xml->headline->paragraphs->children() as $paragraph) {
			if (in_array($filter_selected, str_getcsv($paragraph['filters']))) {
				echo "            <p class='small'>".get_in_lang($paragraph, $language_selected)."</p>\n";
			}
		}
		
		echo "          </td>\n";
		echo "        </tr>\n";
		echo "        <tr>\n";
		echo "          <td style='padding: 5px;'>\n";
		
		echo "            <table>\n";
		echo "              <tr>\n";
		echo "                <td colspan='2' style='padding: 5px;'>\n";
		
		// Work experience
		echo "                <h1 class='stylish'>".get_in_lang($xml->work_experience->title, $language_selected)."</h1>\n";
		foreach ($xml->work_experience->experiences->children() as $experience) {
			if (in_array($filter_selected, str_getcsv($experience['filters']))) {
				echo "                <table>\n";
				echo "                  <tr>\n";
				echo "                    <td align='left'>\n";
				echo "                      <h2 class='stylish'>".get_in_lang($experience->enterprise, $language_selected)."</h2>\n";
				
				foreach ($experience->positions->children() as $position) {
					echo "                      <h3 class='stylish'>&bull; ".get_in_lang($position->title, $language_selected)."</h3>\n";
					
					echo "                      <p class='p3b_small'>".$position->location." | ".$position->start_date." - ".get_in_lang($position->end_date, $language_selected)." | ";
					foreach ($position->details->children() as $paragraph) {
						if (in_array($filter_selected, str_getcsv($paragraph['filters']))) {
							echo " ".get_in_lang($paragraph, $language_selected);
						}
					}
					echo "      </p>\n";
				}
				
				echo "                    </td>\n";
				echo "                    <td class='logo' align='center' width='35px'>\n";
				echo "                      <img src='data/pictures/".$experience->logo."' width='35px' height='35px' />\n";
				echo "                    </td>\n";
				echo "                  </tr>\n";
				echo "                </table>\n";
			}
		}
		
		echo "                </td>\n";
		echo "              </tr>\n";
		
		echo "              <tr>\n";
		echo "                <td width='50%' style='padding: 5px;'>\n";
		
		// Education
		echo "                <h1 class='stylish'>".get_in_lang($xml->education->title, $language_selected)."</h1>\n";
		foreach ($xml->education->experiences->children() as $experience) {
			if (in_array($filter_selected, str_getcsv($experience['filters']))) {
				echo "                <table>\n";
				echo "                  <tr>\n";
				echo "                    <td align='left'>\n";
				echo "                      <h2 class='stylish'>".get_in_lang($experience->institution, $language_selected)."</h2>\n";
				
				foreach ($experience->careers->children() as $career) {
					if (in_array($filter_selected, str_getcsv($career['filters']))) {
						echo "                      <h3 class='stylish'>&bull; ".get_in_lang($career->title, $language_selected)."</h3>\n";
						
						$location = in_array($filter_selected, str_getcsv($career->location['filters'])) ? $career->location." | " : "";
						$start_date = in_array($filter_selected, str_getcsv($career->start_date['filters'])) ? $career->start_date." - " : "";
						$end_date = in_array($filter_selected, str_getcsv($career->end_date['filters'])) ? get_in_lang($career->end_date, $language_selected) : "";
						
						echo "                      <p class='position_info_small'>".$location.$start_date.$end_date."</p>\n";
						
						echo "                      <p class='p3b_small'>\n";
						foreach ($career->details->children() as $paragraph) {
							if (in_array($filter_selected, str_getcsv($paragraph['filters']))) {
								echo " ".get_in_lang($paragraph, $language_selected);
							}
						}
						echo "      </p>\n";
					}
				}
				
				echo "                    </td>\n";
				echo "                    <td class='logo' align='center' width='35px'>\n";
				echo "                      <img src='data/pictures/".$experience->logo."' width='35px' height='35px' />\n";
				echo "                    </td>\n";
				echo "                  </tr>\n";
				echo "                </table>\n";
			}
		}
		
		echo "                </td>\n";
		echo "                <td width='50%' style='padding: 5px;'>\n";
		
		// Courses
		echo "                  <h1 class='stylish'>".get_in_lang($xml->courses->title, $language_selected)."</h1>\n";
		
		$courses = make_sortable($xml->courses->courses->children());
		usort($courses, 'compare_end_date');
		
		foreach ($courses as $course) {
			if (in_array($filter_selected, str_getcsv($course['filters']))) {
				$duration = (in_array($filter_selected, str_getcsv($course->duration['filters'])) && ($course->duration != "")) ? " | ".$course->duration."h" : "";
				echo "                  <p class='p3_small' style='padding-left: 0cm; margin-left: 0cm;'>".get_in_lang($course->title, $language_selected)." | ".get_in_lang($course->institution, $language_selected)."</p>\n";
			}
		}
		
		echo "                </td>\n";
		echo "              </tr>\n";
		echo "            </table>\n";
		
		echo "          </td>\n";
		echo "        </tr>\n";
		echo "      </table>\n";
		
		echo "    </td>\n";
		echo "  </tr>\n";
		echo "</table>\n";
	}
?>
</div>
</body>
</html>

<?php
	}
} else {
	exit("Error opening $file.");
}
?>
