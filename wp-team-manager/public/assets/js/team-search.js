jQuery(document).ready(function($){
  $('.dwl-team-search').on('keyup', function(){
    let keyword = $(this).val();
    let postId = $(this).data('post-id');
    let container = $(this).closest('.dwl-team-wrapper').find('.dwl-team-wrapper--main');
    let settings = container.closest('.dwl-team-wrapper').data('settings');
console.log(keyword);
    $.ajax({
      url: dwlTeamSearch.ajax_url,
      type: 'POST',
      data: {
        action: 'dwl_team_member_search',
        nonce: dwlTeamSearch.nonce,
        keyword: keyword,
        post_id: postId,
        settings: settings
      },
      success: function(response) {
        if(response.success) {
          container.html(response.data);
        }
      }
    });
  });
});