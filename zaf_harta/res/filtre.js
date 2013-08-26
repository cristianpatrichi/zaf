function getHTTPObject(){
	var xmlhttp;
	// Attempt to initialize xmlhttp object
	try {
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	}
    catch (e) {
		// Try to use different activex object
		try {
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		catch (E) {
			xmlhttp = false;
		}
	}
	// If not initialized, create XMLHttpRequest object
	if (!xmlhttp) {
		if (typeof XMLHttpRequest != 'undefined') {
			xmlhttp = new XMLHttpRequest();
		}
		else {
			alert("This browser does not support AJAX.");
			return null;
		}
	}
	return xmlhttp;
}

function setOutput(){ 
	var inner="<option value=''>Toate</option>";
	if(httpObject.readyState == 4){		
		var response = httpObject.responseText;
		var items = response.split(";");
		var count = items.length;
		for (var i=1;i<count;i++) {
			if (items[i] == '') continue;
			var options = items[i].split("-");
			if (options[1] == 'false') {
				inner+="<option value='"+options[0]+"'>"+options[0]+"</option>"
			} else {		
				inner+="<option value='"+options[0]+"' selected>"+options[0]+"</option>"	
			}				
		}
		document.getElementById('cont_localitati_a').innerHTML="Localitati <select name=\'localitate_a\' id=\'localitate_a\'>"+inner+"</select>";
		document.getElementById('cont_localitati_i').innerHTML="Localitati <select name=\'localitate_i\' id=\'localitate_i\'>"+inner+"</select>";
	} else {
		document.getElementById('cont_localitati_a').innerHTML="Localitati <select name=\'localitate_a\' id=\'localitate_a\'><option value=''>asteptati...</option></select>";
		document.getElementById('cont_localitati_i').innerHTML="Localitati <select name=\'localitate_a\' id=\'localitate_a\'><option value=''>asteptati...</option></select>";
	}
}


function arata_localitatile(reg){
	httpObject = getHTTPObject();
	if (httpObject != null) {
		loc = '/orase/arata_localitati_filtre/' + reg;
		httpObject.open("GET", loc, true);
		httpObject.onreadystatechange = setOutput;
		httpObject.send(null);
	}
}

function in_array(string, array)
{
  for (i = 0; i < array.length; i++)
  {
      if(array[i] == string)
     {
       return true;
     }
  }
	return false;
}

function changeCategory(categorie) {
	var categorii_auto= new Array('automobile');
	var categorii_imobiliare= new Array('apartamente','garsoniere','case-vile','spatii comerciale-birouri','terenuri');
	var display;
	display=(in_array(categorie,categorii_auto)?'block':'none');
	document.getElementById('filtre_auto').style.display=display; 
	display=(in_array(categorie,categorii_imobiliare)?'block':'none');
	document.getElementById('filtre_imobiliare').style.display=display; 					
	display=(((in_array(categorie,categorii_imobiliare) && categorie!='garsoniere' && categorie!='terenuri') && (document.getElementById('oferta_vanzare').checked || document.getElementById('oferta_inchiriere').checked || document.getElementById('schimb').checked))?'block':'none');
	document.getElementById('nr_camere').style.display=display;
	display=((in_array(categorie,categorii_imobiliare) && !document.getElementById('cerere_cumparare').checked && (document.getElementById('oferta_vanzare').checked || document.getElementById('oferta_inchiriere').checked))?'block':'none');
	document.getElementById('var_moneda').style.display=display;
	display=((in_array(categorie,categorii_imobiliare) && categorie!='terenuri' && (document.getElementById('oferta_vanzare').checked))?'block':'none');
	document.getElementById('var_pret_van').style.display=display;
	display=((categorie=='terenuri' && (document.getElementById('oferta_vanzare').checked))?'block':'none');
	document.getElementById('var_pret_van_ter').style.display=display;
	display=((in_array(categorie,categorii_imobiliare) && (document.getElementById('oferta_inchiriere').checked))?'block':'none');
	document.getElementById('var_pret_inc').style.display=display;
	display=(categorie=='automobile')?'block':'none';
	document.getElementById('cont_combustibil').style.display=display; 
	display=(categorie=='automobile' || categorie=='motociclete')?'block':'none';
	document.getElementById('cont_km_an').style.display=display; 
	/*
	var height;
	width=getBrowserWidth();
	height = ((in_array(categorie, categorii_auto)) || (in_array(categorie, categorii_imobiliare))) ? '87px' : '41px';
	
	document.getElementById('search_bar_height').style.height=height;
	document.getElementById('search_bar').style.height=height;
	*/
	//resetare filtre imobiliare
	if(document.getElementById('pret_min_i_inc')) document.getElementById('pret_min_i_inc').value='';
	if(document.getElementById('pret_max_i_inc')) document.getElementById('pret_max_i_inc').value='';
	if(document.getElementById('pret_min_i_van')) document.getElementById('pret_min_i_van').value='';
	if(document.getElementById('pret_max_i_van')) document.getElementById('pret_max_i_van').value='';
	if(document.getElementById('pret_min_i_van_ter')) document.getElementById('pret_min_i_van_ter').value='';
	if(document.getElementById('pret_max_i_van_ter')) document.getElementById('pret_max_i_van_ter').value='';
	if(document.getElementById('moneda_i')) document.getElementById('moneda_i').value='';
	if(document.getElementById('camere')) document.getElementById('camere').value='';
	//resetare filtre auto
	if(document.getElementById('pret_min_a')) document.getElementById('pret_min_a').value='';
	if(document.getElementById('pret_max_a')) document.getElementById('pret_max_a').value='';
	if(document.getElementById('moneda_a')) document.getElementById('moneda_a').value='';
	if(document.getElementById('km_max')) document.getElementById('km_max').value='';
	if(document.getElementById('an_max')) document.getElementById('an_max').value='';
	if(document.getElementById('combustibil')) document.getElementById('combustibil').value='';
	
	change_search_zone_text();
}

function changeCities(judet) {
	if(judet!='Romania' && judet.substr(0,13)!='judete-vecine') { 
		arata_localitatile(judet);
	} else {
		document.getElementById('cont_localitati_a').innerHTML='';
		document.getElementById('cont_localitati_i').innerHTML='';
	}
}

function changeTranzaction() {
	var display;
	var categorie = document.getElementById('categorie').value;
	display = ((document.getElementById('oferta_vanzare').checked || document.getElementById('oferta_inchiriere').checked || document.getElementById('schimb').checked) && categorie!='terenuri' && categorie!='garsoniere')?'block':'none';
	document.getElementById('nr_camere').style.display=display;
	display = ((document.getElementById('oferta_vanzare').checked || document.getElementById('oferta_inchiriere').checked))?'block':'none';
	document.getElementById('var_moneda').style.display=display;
	display = (document.getElementById('oferta_vanzare').checked && categorie!='terenuri')?'block':'none';
	document.getElementById('var_pret_van').style.display=display;
	display = (document.getElementById('oferta_vanzare').checked && categorie=='terenuri')?'block':'none';
	document.getElementById('var_pret_van_ter').style.display=display;
	display = (document.getElementById('oferta_inchiriere').checked)?'block':'none';
	document.getElementById('var_pret_inc').style.display=display;
	
	document.getElementById('pret_min_i_inc').value='';
	document.getElementById('pret_max_i_inc').value='';
	document.getElementById('pret_min_i_van').value='';
	document.getElementById('pret_max_i_van').value='';
	document.getElementById('pret_min_i_van_ter').value='';
	document.getElementById('pret_max_i_van_ter').value='';
	document.getElementById('moneda_i').value='';
	document.getElementById('camere').value='';
}

function change_search_zone_text() {
	var categorii_imobiliare= new Array('imobiliare','apartamente','garsoniere','case-vile','spatii comerciale-birouri','terenuri');
	var categorii_auto =  new Array('auto', 'automobile', 'motociclete', 'piese-accesorii', 'alte vehicule');
	
	var categorie = document.getElementById('categorie').value;
	var judet = document.getElementById('judet_search').value;
	var cuvant = document.getElementById('cuvant');
	if (!cuvant.value || cuvant.value == 'Cauta zona sau alte detalii' || cuvant.value == 'Cauta marca, model, alte detalii') {
		if (in_array(categorie,categorii_imobiliare)) {
			cuvant.className = 'grey_text';
			cuvant.value = 'Cauta zona sau alte detalii';
		} else if (in_array(categorie, categorii_auto)) {
			cuvant.className = 'grey_text';
			cuvant.value = 'Cauta marca, model, alte detalii';
		} else {
			cuvant.value = '';
			cuvant.className = '';
		}
	}
}

function click_search_text(search_input) {
	if (search_input.value == 'Cauta zona sau alte detalii' || search_input.value == 'Cauta marca, model, alte detalii') { 
		search_input.value=''; 
		search_input.className = ''; 
	}
}