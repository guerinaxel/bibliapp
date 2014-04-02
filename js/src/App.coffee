$ ->
	$('#rechercheInternet').click(->
		$.getJSON('https://www.googleapis.com/books/v1/volumes?q=isbn:'+$("#isbn").val(), (data) ->		
			if data.totalItems == 0
				alert("Il n'y a pas de livre associé à l'ISBN saisi")
			
			$("#titre").val(data.items[0].volumeInfo.title)
			$("#description").val(data.items[0].volumeInfo.description)
			$("#auteur").val(data.items[0].volumeInfo.authors)
			$("#langue").val(data.items[0].volumeInfo.language)
			$("#publication").val(data.items[0].volumeInfo.publishedDate)
		)
	)
	bootbox.setDefaults(locale: "fr")
	$('#confirm').click((e) ->
		e.preventDefault()
		targetUrl = $(this).attr("href")
		console.log(targetUrl) 
		bootbox.confirm("Êtes-vous sûr ?", (result)->
			if result 
				window.location.href = targetUrl;	
			else
				$(this).modal('hide')
		)	 
	)