<?php session_start();
function parse_date($time, $form_lineaire=false)
{
	if($form_lineaire)
		return 'le '.date('d/m/Y \à H\hi', $time);
	else {
		if ($time <= time()) {
			if (date('d', time()) == date('d', $time) AND date('m', time()) == date('m', $time) AND date('Y', time()) == date('Y', $time)) // aujourd'hui
			{
				if (time() - $time <= 2 * 60 * 60) //Il y a moins de deux heures
				{
					$diff = time() - $time;
					if ($diff < 60) //Il y a  moins d'une minute
					{
						return 'il y a ' . $diff . 's';
					} else {
						if (ceil($diff / 60) < 60) //Il y a moins d'une heure
						{
							return 'il y a ' . round($diff / 60) . 'm';
						} else //Entre [1 et 2] hre
						{
							$min = floor(($diff / 3600 - floor($diff / 3600)) * 60);
							if ($min < 10)
								return 'il y a ' . floor($diff / 3600) . 'h0' . $min . 'm'; //On rajoute un "0" pour xxh0y
							else
								return 'il y a ' . floor($diff / 3600) . 'h' . $min . 'm';
						}
					}
				} else
					return 'aujourd\'hui à ' . date('H\hi', $time);
			} else {
				$false_today = time() - (24 * 60 * 60);
				if (date('d', $false_today) == date('d', $time) AND date('m', $false_today) == date('m', $time) AND date('Y', $false_today) == date('Y', $time)) // Hier
					return 'hier à ' . date('H\hi', $time);
				else
					return 'le ' . date('d/m/Y \à H\hi', $time);
			}
		} else
			return 'le ' . date('d/m/Y \à H\hi', $time); //futur
	}
}

?>