function update_data(jid)
{
	// set access_date to now() only if == 0
	jQuery.ajax({
			method : 'post',
			url : '/sites/all/modules/custom/pw_docgen/update_data.php',
			cache: false,
			data :
			{
				jid : jid,
			},
	});
}
