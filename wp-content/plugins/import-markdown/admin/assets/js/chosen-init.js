(function($) {

  'use strict';

  $(document).ready(function() {

    'use strict';

    //initialize chosen on all the select elements

    var chosen_elements = [];

    if ($('#daimma-import-post-type').length) {
      chosen_elements.push('#daimma-import-post-type');
    }
    if ($('#daimma-markdown-parser').length) {
      chosen_elements.push('#daimma-markdown-parser');
    }
    if ($('#daimma-cebe-markdown-html5').length) {
      chosen_elements.push('#daimma-cebe-markdown-html5');
    }
    if ($('#daimma-cebe-markdown-keep-list-start-number').length) {
      chosen_elements.push('#daimma-cebe-markdown-keep-list-start-number');
    }
    if ($('#daimma-cebe-markdown-enable-new-lines').length) {
      chosen_elements.push('#daimma-cebe-markdown-enable-new-lines');
    }

    $(chosen_elements.join(',')).chosen();

  });

})(window.jQuery);