<?php

echo '
<h6 class="title">' . t('Liste de vos modèles de documents') . '</h6>
<p><i>' . t('Pour pouvoir télécharger, il faut remplir le document au moins jusqu\'à "Objet du contrat"') . '</i></p>';

global $user;

$links = generate_links($user->uid);


echo '
<ul class="dlinks">';

foreach ($links as $link)
{
	echo '
		<li>
			' . $link['title'] . ' (' . date('d/m/Y', $link['pdate']) . ') - ';
			
	if ($link['status'] == 'downloaded')
	{
		echo t('Éditer');
	}
	else
	{
		echo l(t('Éditer'), 'pwdocgen/'.$link['jid'].'/'.$link['juid'].'/edit', ['attributes' => ['target' => '_blank']]);
	}
		
	echo ' - ';

	if ($link['status'] == 'incomplete')
	{
		echo t('Télécharger');
	}
	else
	{
		echo '<a href="' . create_link($link['jid'], $link['juid'], 'download') . '" onclick="update_status(' . $link['jid'] . ');" target="iframe1">' . t('Télécharger') . '</a>';
	}

	echo ' - ' . t('Statut :') . ' ' . $link['status'] . '
		</li>';
}

echo '

</ul>
';
?>
