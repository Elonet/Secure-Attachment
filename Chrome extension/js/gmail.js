chrome.webRequest.onBeforeRequest.addListener(function(details){
	var url = details.url;
	//ajouter restriction à gmail sur l'url
	if( url.indexOf("&act=sm") > -1 ){
		var pos = 0;
		var num = -1;
		var i = -1;
		while(pos != -1){
			pos = String(details.requestBody.formData['body']).indexOf("attachment/?", i + 1 );
			var id_deb_index = String(details.requestBody.formData['body']).indexOf("folder=", i + 1 )+7;
			var id_fin_index = id_deb_index+33;
			var id = String(details.requestBody.formData['body']).substring(id_deb_index,id_fin_index);
			var mails = details.requestBody.formData['to']+details.requestBody.formData['cc'];
			
			mails = String(mails).split(",");
			for( var i = 0, l = mails.length; i < l; ++i ) {
				if( String(mails[i]).indexOf("<", i + 1 ) > -1 ){
					var mail_deb_index = String(mails[i]).indexOf("<", i + 1 )+1;
					var mail_fin_index = String(mails[i]).indexOf(">", i + 1 );
					mails[i] = String(mails[i]).substring(mail_deb_index, mail_fin_index);
				}
			}
			mails = mails.join(",");
			var data = "mail="+mails+"&subject="+details.requestBody.formData['subject']+"&id="+id+"&fm="+details.requestBody.formData['from'];
			//récup url dans le storage
			var url = "https://[your-upload_url]/managed_attachment.php";
			var xhr = new XMLHttpRequest();
			xhr.open('POST', url, true);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xhr.send(data);
			num += 1;
			i = pos;			
		}
		return details.requestBody;
	}
	
	if(url.indexOf("send.php") > -1 && url.indexOf("?fm=") == -1 ){
		// details.url = details.url+"?fm="+result;
		return { redirectUrl : details.url };
	}
	
},
{urls: [ "<all_urls>" ]},['requestBody','blocking']);
