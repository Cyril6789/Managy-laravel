<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 10/02/2016
 * Time: 08:21
 */

$current_folder = getcwd();
$tab = explode('/', $current_folder);
$lg = count($tab);


if($tab[$lg - 1] == 'managy')
    $path = '.';

if($tab[$lg - 1] == 'ajax')
    $path = '..';

require_once($path.'/templates/v4/classes/Col.class.php');
require_once($path.'/templates/v4/classes/WidgetBox.class.php');
require_once($path.'/templates/v4/classes/HtmlTable.class.php');
require_once($path.'/templates/v4/classes/Label.class.php');
require_once($path.'/templates/v4/classes/ProgressBar.class.php');
require_once($path.'/templates/v4/classes/Button.class.php');
require_once($path.'/templates/v4/classes/Accordion.class.php');
require_once($path.'/templates/v4/classes/Alert.class.php');
require_once($path.'/templates/v4/classes/Danger.class.php');
require_once($path.'/templates/v4/classes/Success.class.php');
require_once($path.'/templates/v4/classes/Info.class.php');
require_once($path.'/templates/v4/classes/Warning.class.php');
require_once($path.'/templates/v4/classes/Tab.class.php');
require_once($path.'/templates/v4/classes/Feeds.class.php');
require_once($path.'/templates/v4/classes/Row.class.php');
require_once($path.'/templates/v4/classes/FormLayout.class.php');
require_once($path.'/templates/v4/classes/Modal.class.php');
require_once($path.'/templates/v4/classes/Notify.class.php');
require_once($path.'/templates/v4/classes/Calendar.class.php');
require_once($path.'/templates/v4/classes/Mail_Form.class.php');
require_once($path.'/templates/v4/classes/SMS_form.class.php');
require_once($path.'/templates/v4/classes/DropdownButton.class.php');
require_once($path.'/templates/v4/classes/Pattern.class.php');
require_once($path.'/templates/v4/classes/PopOver.class.php');
require_once($path.'/templates/v4/classes/Ribbon.class.php');
require_once($path.'/templates/v4/classes/Badge.class.php');
require_once($path.'/templates/v4/classes/DropdownMenu.class.php');
require_once($path.'/templates/v4/classes/Font.class.php');

require_once($path.'/templates/v4/classes/Form/Textarea.class.php');
require_once($path.'/templates/v4/classes/Form/Hidden.class.php');
require_once($path.'/templates/v4/classes/Form/Select.class.php');
require_once($path.'/templates/v4/classes/Form/Text.class.php');
require_once($path.'/templates/v4/classes/Form/Password.class.php');
require_once($path.'/templates/v4/classes/Form/Email.class.php');
require_once($path.'/templates/v4/classes/Form/Form.class.php');
require_once($path.'/templates/v4/classes/Form/Radio.class.php');
require_once($path.'/templates/v4/classes/Form/Checkbox.class.php');
require_once($path.'/templates/v4/classes/Form/Switches.class.php');
require_once($path.'/templates/v4/classes/Form/File.class.php');
require_once($path.'/templates/v4/classes/Form/Spinner.class.php');

require_once($path.'/templates/v4/classes/Graphs/GraphsLines.class.php');
require_once($path.'/templates/v4/classes/Graphs/GraphsPies.class.php');

?>