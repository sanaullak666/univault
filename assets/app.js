$(function(){
  // Lightweight AJAX search helper, used by the resource search page if enabled.
  let timer = null;
  $('#live-search').on('input', function(){
    clearTimeout(timer);
    const q = this.value;
    timer = setTimeout(function(){
      $.getJSON('api/search.php', {q: q}, function(data){
        const box = $('#live-search-results');
        box.empty();
        (data.results || []).forEach(function(r){
          $('<a>').attr('href', 'resource.php?id=' + r.id).text(r.title + ' · ' + (r.code || r.type)).appendTo(box);
        });
      });
    }, 250);
  });

  // File size validation feedback (max 20 MB).
  $('input[type=file]').on('change', function(){
    const f = this.files[0];
    if (f && f.size > 20 * 1024 * 1024) {
      alert('File exceeds the 20 MB limit.');
      this.value = '';
    }
  });
});
