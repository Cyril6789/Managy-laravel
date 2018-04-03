<?php session_start();

	if($_SESSION['gerant'])
	{

		$modalites = new DatabaseWorker('modalites', false);
		$modalites->setWidget('Textes de modalités');
		$modalites->displayedFields(Array('entree', 'sortie', 'site'));
		$modalites->labelsDisplayedFields(Array('Modalités sur fiches entrées', 'Modalités sur fiches sorties', 'Modalités sur fiche intervention sur site'));
		$modalites->activeModify('./modalites');

		$ModulContent = $modalites;
	}
	else
	{
		if(is_file('./templates/'.TEMPLATE_NAME.'/pages/no_right.php'))
		{
			ob_start();
			include('./templates/'.TEMPLATE_NAME.'/pages/no_right.php');
			$ModulContent = ob_get_contents();
			ob_end_clean();
		}
	}

?>