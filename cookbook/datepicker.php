<?php if (!defined('PmWiki')) exit(); 

$RecipeInfo['datepicker']['1.04'] = '2015-02-05'; 

/*
  pmwiki recipe
  add a datepicker-button to a form
 
  based on javascript-code written by Julian Robichaux -- http://www.nsftools.com ,version 1.5, December 4, 2005
  
  more info see pmwiki.org/Cookbook/Datepicker
  
  author: Knut Alboldt
 
  changes:
    6.2.2010 - first implementation
    7.2.2010 - error corrections:
               - parsing of multiple marksups in one line (greedy match)
               - translation of weekdays and monthnames was missing
    7.2.2010 - error corrections
               - placed css and js into pub/datapicker
               - name of $DatepickerImg file
             - var to decide if the javascript and css code is generated only on datapicker-pages or general
    9.2.2010 - added class="inputbutton" to the <input>-tags 
    5.2.2015 - Fixed markup for PHP5.5+ and French translation (by Farvardin)

  t.b.d's:
  - check date values using javascript function datePickerClosed(dateField)
  - try to interpret incomplete date formats like "1." "1.8."  "2.2010" etc
  - add year selector to calendarbox
*/
  
  // @DEBUG(__file__,1);

#-------------
# conguration section - change as you like - but best in you config.php !

# url of where datepicker-files (accessible form webbrowser) are placed (must NOT end with an / )
SDV($DatepickerDirUrl,"$FarmPubDirUrl/datepicker");

# Style of datepicker-box can be changed in these files:
SDV($DatepickerCSS,'datepicker-default.css');

# use image instead of text-button, iconfile must be placed in pub-dir
#SDV($DatepickerImg,""); // dont't use a datapicker image 
SDV($DatepickerImg,"$DatepickerDirUrl/datepickerselect.gif");

# language specific formats

# date formats
# see http://en.wikipedia.org/wiki/Calendar_date#List_of_the_world_locations_by_date_format_in_use
SDV($DatepickerFormat,'d.m.Y');
#SDV($DatepickerFormat,'Ymd'); 
#SDV($DatepickerFormat,'Y-m-d'); HTMLStylesFmt
#SDV($DatepickerFormat,'d-m-Y'); 
#SDV($DatepickerFormat,'d/m/Y'); 
#SDV($DatepickerFormat,'m/d/Y'); 
// these formats must also be defined within function getDateString(dateVal) in file datepicker-nsftool.js !


/* you have to change translations as well (e.g. on page PmWikiDe.XLPageLocal)
*/

# end of user configuration
#-------------

# markup-format: (:datepicker field=FIELDNAME [ button=today|tomorrow ] [ usetextbutton=1 ]:)

Markup('datepicker',
       'directives',
       '/\(:datepicker\s*(.+?):\)/',
       "datepicker");

$HTMLHeaderFmt['datepicker'] = "\n<!-- datepicker nsftools -->\n"
    ."<script type='text/javascript' language='JavaScript1.2'>".datepicker_translate()."</script>\n"
    ."<script type='text/javascript' language='JavaScript1.2' src='$DatepickerDirUrl/datepicker.js'></script>\n"
    ."<link rel='stylesheet' type='text/css' href='$DatepickerDirUrl/$DatepickerCSS' />\n"
    ."<!-- datepicker nsftools end -->\n";


XLSDV('fr',array(
  'select' => 'Choisir',
  'today' => 'aujourd hui',
  'tomorrow' => 'demain',
  'press to select date' => 'appuyez ici pour choisir la date',
  'Su' => 'Dim',
  'Mo' => 'Lun',
  'Tu' => 'Mar',
  'We' => 'Mer',
  'Th' => 'Jeu',
  'Fr' => 'Ven',
  'Sa' => 'Sam',
  'Sunday' => 'Dimanche',
  'Monday' => 'Lundi',
  'Tuesday' => 'Mardi',
  'Wednesday' => 'Mercredi',
  'Thursday' => 'Jeudi',
  'Friday' => 'Vendredi',
  'January' => 'Janvier',
  'February' => 'Février',
  'March' => 'Mars',
  'April' => 'Avril',
  'May' => 'Mai',
  'June' => 'Juin',
  'July' => 'Juillet',
  'August' => 'Août',
  'September' => 'Septembre',
  'October' => 'Octobre',
  'November' => 'Novembre',
  'December' => 'Decembre',
  'Today is'  => 'Aujourd hui c est',
  'this month' => 'mois en cours',
  'close' => 'fermer'
  ));
    
    
function datepicker_translate()
// translate the languagespecific content of the javascript-part
// the corresponding language-specific text has to be translated in a local XLate-Page (page PmWikiXX.XLPageLocal):
/* sample section for german translations:
   # für datepicker
   'select' => 'Datum auswählen'
   'today' => 'heute'
   'tomorrow' => 'morgen'
   'press to select date' => 'Auswahl des Datums aus Kalender'
   'Su' => 'So'
   'Mo' => 'Mo'
   'Tu' => 'Di'
   'We' => 'Mi'
   'Th' => 'Do'
   'Fr' => 'Fr'
   'Sa' => 'Sa'
   'Sunday' => 'Sonntag'
   'Monday' => 'Montag'
   'Tuesday' => 'Dienstag'
   'Wednesday' => 'Mittwoch'
   'Thursday' => 'Donnerstag'
   'Friday' => 'Freitag'
   'January' => 'Januar'
   'February' => 'Februar'
   'March' => 'März'
   'April' => 'Apil'
   'May' => 'Mai'
   'June' => 'Juni'
   'July' => 'Juli'
   'August' => 'August'
   'September' => 'September'
   'October' => 'Oktober'
   'November' => 'November'
   'December' => 'Dezember'   
*/
/*
 Sample for French
   # pour datepicker
   'select' => 'Choisir'
   'today' => 'aujourd hui'
   'tomorrow' => 'demain'
   'press to select date' => 'appuyez ici pour choisir la date'
   'Su' => 'Dim'
   'Mo' => 'Lun'
   'Tu' => 'Mar'
   'We' => 'Mer'
   'Th' => 'Jeu'
   'Fr' => 'Ven'
   'Sa' => 'Sam'
   'Sunday' => 'Dimanche'
   'Monday' => 'Lundi'
   'Tuesday' => 'Mardi'
   'Wednesday' => 'Mercredi'
   'Thursday' => 'Jeudi'
   'Friday' => 'Vendredi'
   'January' => 'Janvier'
   'February' => 'Février'
   'March' => 'Mars'
   'April' => 'Avril'
   'May' => 'Mai'
   'June' => 'Juin'
   'July' => 'Juillet'
   'August' => 'Août'
   'September' => 'Septembre'
   'October' => 'Octobre'
   'November' => 'Novembre'
   'December' => 'Decembre'
   'Today is'  => 'Aujourd hui c est'
   'this month' => 'mois en cours'
   'close' => 'fermer'
    
 * */

{
  $jscode  = "var text_TodayIs   = '".XL('Today is')."';\n"; 
  $jscode .= "var text_ThisMonth = '".XL('this month')."';\n"; 
  $jscode .= "var text_Close     = '".XL('close')."';\n"; 

  // weekday and month strings

  $jscode .= "var dayArrayShort = new Array('".XL('Su')."', '".XL('Mo')."', '".XL('Tu')."', '".XL('We')."', '".XL('Th')."', '".XL('Fr')."', '".XL('Sa')."');\n";

  // you can't just do this:// $jscode .= "var dayArrayShort = new Array('".XL('Mo')."', '".XL('Tu')."', '".XL('We')."', '".XL('Th')."', '".XL('Fr')."', '".XL('Sa')."', '".XL('Su')."');\n";

  $jscode .= "var dayArrayLong = new Array('".XL('Sunday')."', '".XL('Monday')."', '".XL('Tuesday')."', '".XL('Wednesday')."', '".XL('Thursday')."', '".XL('Friday')."', '".XL('Saturday')."');\n";
  $jscode .= "var monthArrayShort = new Array('".XL('Jan')."', '".XL('Feb')."', '".XL('Mar')."', '".XL('Apr')."', '".XL('May')."', '".XL('Jun')."', '".XL('Jul')."', '".XL('Aug')."', '".XL('Sep')."', '".XL('Oct')."', '".XL('Nov')."', '".XL('Dec')."');\n";
  $jscode .= "var monthArrayLong = new Array('".XL('January')."', '".XL('February')."', '".XL('March')."', '".XL('April')."', '".XL('May')."', '".XL('June')."', '".XL('July')."', '".XL('August')."', '".XL('September')."', '".XL('October')."', '".XL('November')."', '".XL('December')."');\n";

  return $jscode;
}

function datepicker($m)
{
  global $DatepickerFormat;
  global $DatepickerImg;
  global $FarmPubDirUrl;
  $args = ParseArgs($m[1]);
  # @DEBUG($args,1);

  $button    = $args['button'] ?? null;
  $format    = $args['format'] ?? null;
  $fieldname = $args['field'] ?? null;

  if (empty($format))
    $format = $DatepickerFormat;

  /*
  // TODO: why did I that ? has to do with fox-fields ...
  $fieldname2 = str_replace('[','_',$fieldname);
  $fieldname2 = str_replace(']','_',$fieldname2);
  */

  // if button= specified, create a button to set today's or tommorow's date
  if ($button == 'today')
    $buttondate = date($format);
  elseif ($button == 'tomorrow')
    $buttondate = date($format,time()+(24*60*60));
   
   
  $code = "\n<!-- DATEPICKER NFSTOOLS -->\n";

  // if icon is defined show image (if not overwritten by option usetextbutton=1
  // <input type="image" ...> does not work (starts reloading, so calendarbox disapears after a second) ! so use <a> + <img> instead
  if (!empty($DatepickerImg) and empty($args['usetextbutton']))
    $code .= '<img src="'.$DatepickerImg.'" alt="['.XL('select').']" ';
  else // text button
    $code .= '<input class="inputbutton" type="button" value="'.XL('select').'" ';
    
  $code .= ' title="'.XL('press to select date').'" onclick="displayDatePicker(\''.$fieldname.'\', this,\''.$format.'\');" >'."\n";
  
  if (!empty($button))
    $code .= '<input class="inputbutton" type="button" value="'.XL($button).'" onclick="updateDateField(\''.$fieldname.'\',\''.$buttondate.'\');">';
    
  $code .= "\n<!-- DATEPICKER NFSTOOLS END -->\n";
 
  return keep($code);
}
?>
