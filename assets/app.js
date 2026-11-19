$(function(){
  // Cascading academic filters on the contribution form.
  function filterOptions(selector, attr, value) {
    $(selector+' option').each(function(){const v=$(this).attr(attr);$(this).toggle(!v||!value||String(v)===String(value));});
    $(selector).val('');
  }
  $('#course').on('change',function(){filterOptions('#subject','data-course',this.value);filterOptions('#module','data-subject','');});
  $('#semester').on('change',function(){const course=$('#course').val();$('#subject option').each(function(){const c=$(this).attr('data-course'),s=$(this).attr('data-semester');$(this).toggle(!c||((!course||c===course)&&(!this.value||!s||s===$('#semester').val())));});$('#subject').val('');});
  $('#subject').on('change',function(){filterOptions('#module','data-subject',this.value);});
  // Lightweight AJAX search helper, used by the resource search page if enabled.
  let timer=null;
  $('#live-search').on('input',function(){clearTimeout(timer);const q=this.value;timer=setTimeout(function(){$.getJSON('api/search.php',{q:q},function(data){const box=$('#live-search-results');box.empty();(data.results||[]).forEach(function(r){$('<a>').attr('href','resource.php?id='+r.id).text(r.title+' · '+(r.code||r.type)).appendTo(box);});});},250);});
  // File name feedback.
  $('input[type=file]').on('change',function(){const f=this.files[0];if(f&&f.size>20*1024*1024){alert('File exceeds the 20 MB limit.');this.value='';}});
});