<?php

require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';

	if(isset($_COOKIE['volplus_org_id'])){
		echo "<h2>" . $_COOKIE['volplus_org_name'] . "</h2>";		
		$orgopportunities = getOrgOpportunities($_COOKIE['volplus_org_id']);
		$orgopportunities  = orgOppsForDisplay($orgopportunities);
		echo "<a href='/manage-opportunity'><button type='button' id='volplus_createOppButton' class='volplus_createOppButton button' style='float:right '>";
		echo "<i class='fa fa-plus-square fa-4x'></i>  New Opportunity</button></a>";
		echo '<h3>Our Opportunities</h3>';
		echo html_table($orgopportunities);

		$orgcontacts = getOrgContacts($_COOKIE['volplus_org_id']);
		$orgcontacts  = orgContactsForDisplay($orgcontacts);
		echo '<br/><h3>Our Contacts</h3>';
		echo html_table($orgcontacts);
		

	}