(function ($) {
  $(document).ready(function ($) {
    var filter = $("#form-events");
    var form = $("[data-js-form=filter-events]");

    form.submit(function (e) {
      e.preventDefault();
      filter.find("#response-content-events").empty();
      filter.find("#response-content-events").append('<div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>');
      if (typeof pag !== "undefined" && pag === true) {
        paginate = paginate
      } else {
        paginate = 1
      }
      pag = false;
      console.log(paginate);
      $('#paginate').hide();
      var data = {
        action: "events_filter",
        search: $("#search").val(),
        type: $("#type").val(),
        paginate: paginate
      };


      $.ajax({
        url: "/wp-admin/admin-ajax.php",
        data: data,
        success: function (response) {
          console.log($.trim(response[0]))
          console.table(response)
          filter.find("#response-content-events").empty();

          if ($.trim(response[0]) === '') {
            $("#response-content-events").html('<h4>No search results for your search criteria</h4>');
          }
          if (response[0]) {
            maximum = response[0].max;
          } else {
            maximum = 1;
          }
          for (var i = 0; i < response.length; i++) {
            var html = `
            <div class="mosaic-resource-bucket">
              <div class="post">
                <div>
                  <div class="featured-image">
                    <img src="${response[i].image}" alt="${response[i].title}">
                  </div>
                  <div class="content">
                    <div class="small">${response[i].category[0].name}</div>
                    <h3 class="h5"><a href="${response[i].button_url}">${response[i].title}</a></h3>
                    <div class="button-wrapper">
                      <a class="button white-button small-button read-more" href="${response[i].button_url}">
                        <span>${response[i].button_text}</span>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          `;
            filter.find("#response-content-events").append(html);
            $('#paginate-events').show();
          }


          $("#paginate-events").empty();
          for (let i = 1; i < maximum + 1; i++) {
            if (maximum > 1) {
              $("#paginate-events").append('<a class="btn-number page-numbers" data-paginate="' + i + '">' + i + '</a>')
            }
          }

          $(".btn-number[data-paginate='" + paginate + "']").addClass('current');
          $(".btn-number").click(function (e) {
            e.preventDefault();
            pag = true;
            paginate = $(this).data("paginate");
            form.submit();
          });
        }
      });
      // Initial triger for ajax pagination
      $(".btn-number").click(function (e) {
        e.preventDefault();
        pag = true;
        paginate = $(this).data("paginate");
        form.submit();
      });
    });
    //Initial display

    //Display on search
    $("form").on("keyup", "#search", function () {
      if ($("#search").val().length > 3) {
        $(this)
          .closest("form")
          .submit();
      } else {
        form.find("#response-content-events").empty();
      }
    });

    // Display on select
    $("form select").on("change", function () {
      $(this)
        .closest("form")
        .submit();
    });

    form.submit();
  });
})(jQuery)