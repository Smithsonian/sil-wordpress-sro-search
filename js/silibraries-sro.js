function sroToggleAdvancedSearch() {
	elAdvanced = document.getElementById('advanced-search');
	if (elAdvanced.style.display == 'none') {
		elAdvanced.style.display = 'block';
	} else {
		elAdvanced.style.display = 'none';	
	}
}

function sroToggleAbstract(elButton) {
  elAbstract = document.getElementById('abstract-'+elButton.dataset.id);
	if (elButton.innerText == 'More...') {
		elAbstract.classList.add('visible');
		elButton.innerText = 'Less...';
	} else {
		elAbstract.classList.remove('visible');
		elButton.innerText = 'More...';
	}  
}

function reloadPage() {
  spin = document.getElementsByClassName('spinner');
  for (i=0; i<spin.length; i++) {
    spin[i].style.display = 'inline-block';
  }
  frm = document.getElementById('sro_results_search');
  frm.submit();
  return false;
}