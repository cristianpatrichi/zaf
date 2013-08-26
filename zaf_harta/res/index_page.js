function preload_image(_image) {
	var image = new Image;
	image.src = _image;
} 
/* 
 * Change area image onmouseover on index page 
 */
function change_image(judet) {
	var ShowItem = document.getElementById("harta");
	var LinkItem = document.getElementById("area_" + judet);
//	ShowItem.style.backgroundImage = 'url(http://media.tocmai.ro/images/judete/' + judet + '.gif)';
	ShowItem.className = judet;
	LinkItem.style.textDecoration = "underline";
	LinkItem.style.color="#669900";
	return true;
}

/* 
 * Change back area image onmouseout on index page
 */ 
function hide_image(judet) {
	var ShowItem = document.getElementById("harta");
	var LinkItem = document.getElementById("area_" + judet);
//	ShowItem.style.backgroundImage = 'url(http://media.tocmai.ro/images/harta.gif)';
	ShowItem.className = '';
	LinkItem.style.textDecoration = "none";
	LinkItem.style.color="#0156A9";
	return true;
}