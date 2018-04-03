<?php session_start();
if(is_file('./templates/v4/classes/includes.php'))
    require_once('./templates/v4/classes/includes.php');
?>
<script language="javascript">
function load_customers(param)
{
        klient = new XMLHttpRequest();
        klient.onreadystatechange = retourClient;
        klient.open("GET", "./ajax/load_customers.php?word="+param+"&redirect_link=<?php echo $redirect_link;?>&type=<?php echo $type;?>&no_parent=<?php echo $no_parent;?>&no_change_page=<?php echo $no_change_page;?>&reload_function=<?php echo $reload_function;?>");
        klient.send(null);
		document.getElementById('customer_name_new').value = param;
        document.getElementById('customer_fname_new').value = param;
}

function retourClient() {

        document.getElementById("result_customers").innerHTML= klient.responseText;
}

</script>
<div class="row">
<?php
$search = new FormLayout(INTER_NEW_CUSTOMER);
$search->setFormControls('search_customer', '');

$text_search = new Text('customer_name', 'customer_name');
$text_search->onKeyUp('load_customers(this.value);');

$search->addLine(INTER_NEW_CUSTOMER_NAME, $text_search);

echo $search;
?>



<div id="result_customers" >

    <?php
    $widget_result = new WidgetBox('Liste des clients trouvés', 6);

    echo $widget_result;

    ?>
</div>


<script type="text/javascript">
window.onload = function(){initAutoComplete(document.getElementById('form1'),
document.getElementById('customer_city'),document.getElementById('submit1'))};

</script>


<?php
$new_customer = new FormLayout(CUSTOMERS_NEW_CUSTOMER, 6);
$new_customer->setFormControls('form1', './customers-create');

$radio_pro = new Radio('type', 'pro');
if($type != '2')
    $radio_pro->checked();
if($type == '2')
    $radio_pro->disabled();
$radio_pro->onChange("document.getElementById('part1').style.display = 'none'; document.getElementById('part2').style.display = 'none'; document.getElementById('part3').style.display = 'none'; document.getElementById('pro').style.display = 'block';");

$radio_part = new Radio('type', 'part');
if($type == '2')
    $radio_part->checked();
if($type == '1')
    $radio_part->disabled();
$radio_part->onChange("document.getElementById('part1').style.display = 'block'; document.getElementById('part2').style.display = 'block'; document.getElementById('part3').style.display = 'block'; document.getElementById('pro').style.display = 'none';");

$redirect_link_hidden = new Hidden('redirect_link');
$redirect_link_hidden->setValue($redirect_link);

$bad_redirect_link_hidden = new Hidden('bad_redirect_link');
$bad_redirect_link_hidden->setValue($bad_redirect_link);

//if($type != '1' AND $type != '2')
$new_customer->addLine('Type', $radio_pro.' '.CUSTOMERS_PRO.'<br />'.$radio_part.' '.CUSTOMERS_PART);

if($type == 0 OR $type == 1)
{
    $customer_name = new Text('customer_name', 'customer_name_new');
    $new_customer->addLine(CUSTOMERS_SOCIETY_NAME, $customer_name, false, 'pro');
}

if($type == 0 Or $type == 2)
{
    $style = '';
    if(!$type)
        $style = "display : none;";

    $genre_mr = new Radio('customer_titre', CUSTOMERS_MISTER, 'customer_titre');
    $genre_mr->checked();
    $genre_miss = new Radio('customer_titre', CUSTOMERS_MISS);
    $genre_misses = new Radio('customer_titre', CUSTOMERS_MISSES);

    $new_customer->addLine('Genre', $genre_mr.' '.CUSTOMERS_MISTER.'<br />'.$genre_miss.' '.CUSTOMERS_MISS.'<br />'.$genre_misses.' '.CUSTOMERS_MISSES, false, 'part1', $style);


    $customer_fname = new Text('customer_fname', 'customer_fname_new');

    $new_customer->addLine(CUSTOMERS_FNAME, $customer_fname, false, 'part2', $style);

    $customer_lname = new Text('customer_lname', 'customer_lname_new');

    $new_customer->addLine(CUSTOMERS_LNAME, $customer_lname, false, 'part3', $style);

}

$email = new Email('customer_mail', 'customer_mail');
$new_customer->addLine(CUSTOMERS_MAIL, $email.$redirect_link_hidden.$bad_redirect_link_hidden);

$adress1 = new Text('customer_adress', 'customer_adress');
$new_customer->addLine(CUSTOMERS_ADRESS, $adress1);

$adress2 = new Text('customer_adress_suite', 'customer_adress_suite');
$new_customer->addLine(CUSTOMERS_ADRESS_SUITE, $adress2);


$cp = new Hidden('customer_cp', 'customer_cp');
$cpac = new Text('auto_v', 'autocomplete-cp');
$city = new Hidden('customer_city', 'customer_city');
$new_customer->addLine(CUSTOMERS_CP.' '.CUSTOMERS_CITY, $cpac.$city.$cp);

$gsm = new Text('customer_gsm', 'customer_gsm');
$gsm->dataMask('+33 (0)9 99 99 99 99');
$new_customer->addLine(CUSTOMERS_GSM, $gsm);

$fixe = new Text('customer_fixe', 'customer_fixe');
$fixe->dataMask('+33 (0)9 99 99 99 99');

$new_customer->addLine(CUSTOMERS_PHONE, $fixe);

$new_customer->setValueButton('Créer');


echo $new_customer;

if($no_change_page)
{
    ?>
    <script>
        $(function(){
            $('#form1').submit(function(e){


                var url = "./../../../../ajax/create_customer.php";

                $.ajax({
                    type: "POST",
                    url: url,
                    data: $("#form1").serialize(), // serializes the form's elements.
                    success: function(data)
                    {
                        //alert(data);
                        if($.isNumeric(data))
                            <?php echo $reload_function;?>(data); // show response from the php script.
                    }
                });

                e.preventDefault();
            });


        });
    </script>
    <?php
}




?>
</div>
