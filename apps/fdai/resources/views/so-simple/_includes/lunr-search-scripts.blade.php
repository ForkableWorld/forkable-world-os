  <script src="https://studies.quantimo.do/assets/js/lunr/lunr.min.js"></script>
  <script src="https://studies.quantimo.do/assets/js/search-data.js"></script>
  <script>
    var idx = lunr(function () {
      // the, the normal lunr index initialization
      this.field('title')
      this.field('excerpt')
      this.field('categories')
      this.field('tags')
      this.ref('id')

      this.pipeline.remove(lunr.trimmer)

      // add documents to index
      for (var item in store) {
        this.add({
          title: store[item].title,
          excerpt: store[item].excerpt,
          categories: store[item].categories,
          tags: store[item].tags,
          id: item
        })
      }
    });

    console.log(jQuery.type(idx));

    $(document).ready(function () {
      $('input#search').on('keyup', function () {
        var resultdiv = $('#results');
        var query = $(this).val().toLowerCase();
        var result =
          idx.query(function (q) {
            query.split(lunr.tokenizer.separator).forEach(function (term) {
              q.term(term, { boost: 100 })
              if (query.lastIndexOf(" ") != query.length - 1) {
                q.term(term, { usePipeline: false, wildcard: lunr.Query.wildcard.TRAILING, boost: 10 })
              }
              if (term != "") {
                q.term(term, { usePipeline: false, editDistance: 1, boost: 1 })
              }
            })
          });
        resultdiv.empty();
        resultdiv.prepend('<p class="results__found">' + result.length + ' Result(s) found</p>');
        for (var item in result) {
          var ref = result[item].ref;
          var searchitem =
            '<article class="entry">' +
              '<h3 class="entry-title">' +
                '<a href="' + store[ref].url + '">' + store[ref].title + '</a>' +
              '</h3>' +
              '<div class="entry-excerpt">' +
                '<p>' + store[ref].excerpt.split(" ").splice(0, 20).join(" ") + '...</p>' +
              '</div>' +
            '</article>';
          resultdiv.append(searchitem);
        }
      });
    });
  </script>
