/***************************************
   Formulaire de contact.
-------------------------------------- */

$(document).ready(function() {

	   alert('theme');    
  $("#theme").change(function() {
      $.ajax({
          type: 'POST',
          dataType: 'json',
          url:  '{{ path("demande_soustheme") }}',
          data: 'id_theme=' + $(this).val(),
          success: function(response)
          {
              $('#sousTheme').find("option").remove();
              $.each(response, function(i, item) {
                  $('#sousTheme').append(new Option(item, i));
              });
          }
      });
   
  // Case à cocher : champs de texte conditionnels.
  
  $('input.conditional').next('span').andSelf().hide()
  .end().end()
  .each(function() {
    var $thisInput = $(this);
    var $thisFlag = $thisInput.next('span');
    $thisInput.prev('label').find(':checkbox')
    .attr('checked', false)
    .click(function() {
      if (this.checked) {
        $thisInput.show().addClass('required');
        $thisFlag.show();
        $(this).parent('label').addClass('req-label');
      } else {
        $thisInput.hide().removeClass('required').blur();
        $thisFlag.hide();
        $(this).parent('label').removeClass('req-label');
      }
    });
  });

  // Valider les champs sur l'événement blur.
  
  $('form :input').blur(function() {
    $(this).parents('li:first').removeClass('warning')
    .find('span.error-message').remove();
    
    if ($(this).hasClass('required')) {
      var $listItem = $(this).parents('li:first');
      if (this.value == '') {
        var errorMessage = 'Ce champ doit être rempli';
        if ($(this).is('.conditional')) {
          errorMessage += ', lorsque la case correspondante est cochée';
        }
        $('<span></span>')
          .addClass('error-message')
          .text(errorMessage)
          .appendTo($listItem);
        $listItem.addClass('warning');
      }
    }

    if (this.id == 'email') {
      var $listItem = $(this).parents('li:first');
      if ($(this).is(':hidden')) {
        this.value = '';
      }
      if (this.value != '' && 
      !/.+@.+\.[a-zA-Z]{2,4}$/.test(this.value)) {
        var errorMessage = "Veuillez utiliser un format d’adresse valide"
                                  + ' (par exemple paul@example.com)';
        $('<span></span>')
          .addClass('error-message')
          .text(errorMessage)
          .appendTo($listItem);
        $listItem.addClass('warning');
      }
    }
  });

  // Valider le formulaire lors de la soumission.
  
  $('form').submit(function() {
    $('#submit-message').remove();
    $(':input.required').trigger('blur');
    var numWarnings = $('.warning', this).length;
      if (numWarnings) {
        var fieldList = [];
        $('.warning label').each(function() {
          fieldList.push($(this).text());
        });
        $('<div></div>')
        .attr({
          'id': 'submit-message', 
          'class': 'warning'
        })
        .append('Veuillez corriger les erreurs dans les ' + 
                                      numWarnings + ' champs suivants&nbsp;:<br />')
        .append('&bull; ' + fieldList.join('<br />&bull; '))
        .insertBefore('#send');
      return false;
    };
  });

  // Cases à cocher.
  $('form :checkbox').removeAttr('checked');

  // Cases à cocher avec (dé)cocher tout.
  $('<li></li>')
  .html('<label><input type="checkbox" id="discover-all" />' + 
                                ' <em>cocher tout</em></label>')
  .prependTo('li.discover > ul');
  $('#discover-all').click(function() {
    var $checkboxes = $(this) .parents('ul:first')
                                    .find(':checkbox');
    if (this.checked) {
      $(this).next().text(' décocher tout');
      $checkboxes.attr('checked', true);
    } else {
      $(this).next().text(' cocher tout');
      $checkboxes.attr('checked', '');
    };
  })
  .parent('label').addClass('checkall');
});

});

/***************************************
   Texte substituable.
-------------------------------------- */

$(document).ready(function() {
  var $search = $('#search').addClass('overlabel');
  var $searchInput = $search.find('input');
  var $searchLabel = $search.find('label');
  
  if ($searchInput.val()) {
    $searchLabel.hide();
  }

  $searchInput
  .focus(function() {
    $searchLabel.hide();
  })
  .blur(function() {
    if (this.value == '') {
      $searchLabel.show();
    }
  });
  
  $searchLabel.click(function() {
    $searchInput.trigger('focus');
  });
});


/***************************************
   Champ de recherche à auto-complétion.
-------------------------------------- */

$(document).ready(function() {
  var $autocomplete = $('<ul class="autocomplete"></ul>')
  .hide()
  .insertAfter('#search-text');
  var selectedItem = null;

  var setSelectedItem = function(item) {
    selectedItem = item;

    if (selectedItem === null) {
      $autocomplete.hide();
      return;
    }

    if (selectedItem < 0) {
      selectedItem = 0;
    }
    if (selectedItem >= $autocomplete.find('li').length) {
      selectedItem = $autocomplete.find('li').length - 1;
    }
    $autocomplete.find('li').removeClass('selected')
      .eq(selectedItem).addClass('selected');
    $autocomplete.show();
  };

  var populateSearchField = function() {
    $('#search-text').val($autocomplete
    .find('li').eq(selectedItem).text());
    setSelectedItem(null);
  };

  $('#search-text')
  .attr('autocomplete', 'off')
  .keyup(function(event) {
    if (event.keyCode > 40 || event.keyCode == 8) {
      // Les touches ayant les codes 40 et inférieurs sont particulières
      // (Entrée, touches de direction, Échap, etc.).
      // Le code 8 correspond à la touche Retour arrière.
      $.ajax({
        'url': '../search/autocomplete.php',
        'data': {'search-text': $('#search-text').val()},
        'dataType': 'json',
        'type': 'GET',
        'success': function(data) {
          if (data.length) {
            $autocomplete.empty();
            $.each(data, function(index, term) {
              $('<li></li>').text(term).appendTo($autocomplete).mouseover(function() {
                setSelectedItem(index);
              }).click(populateSearchField);
            });

            setSelectedItem(0);
          }
          else {
            setSelectedItem(null);
          }
        }
      });
    }
    else if (event.keyCode == 38 && 
                                 selectedItem !== null) {
      // Appui sur la touche Flèche vers le haut.
      setSelectedItem(selectedItem - 1);
      event.preventDefault();
    }
    else if (event.keyCode == 40 && 
                                 selectedItem !== null) {
      // Appui sur la touche Flèche vers le bas.
      setSelectedItem(selectedItem + 1);
      event.preventDefault();
    }
    
    else if (event.keyCode == 27 && selectedItem !== null) {
      // Appui sur la touche Échap.
      setSelectedItem(null);
    }
  }).keypress(function(event) {
    if (event.keyCode == 13 && selectedItem !== null) {
      // Appui sur la touche Entrée.
      populateSearchField();
      event.preventDefault();
    }
  }).blur(function(event) {
    setTimeout(function() {
      setSelectedItem(null);
    }, 250);
  });
});


/***************************************
   Panier d'achat.
-------------------------------------- */

$(document).ready(function() {
  var stripe = function() {
    $('#cart tbody tr').removeClass('alt')
    .filter(':visible:odd').addClass('alt');
  };
  stripe();

  $('#recalculate').hide();

  $('.quantity input').keypress(function(event) {
    if (event.which && (event.which < 48 || 
                                       event.which > 57)) {
      event.preventDefault();
    }
  }).change(function() {
    var totalQuantity = 0;
    var totalCost = 0;
    $('#cart tbody tr').each(function() {
      var price = parseFloat($('td.price', this).text()
        .replace(',', '.').replace(/[^\d\.]/g, ''));	
      price = isNaN(price) ? 0 : price;
      var quantity = 
               parseInt($('.quantity input', this).val(), 10);
      quantity = isNaN(quantity) ? 0 : quantity;         
      var cost = quantity * price;
	  var costFR = cost.toFixed(2).replace('.', ',');	  
	  $('td.cost', this).text(costFR + ' €');	  
      totalQuantity += quantity;
      totalCost += cost;
    });
    var subTotalCostFR = totalCost.toFixed(2).replace('.', ',');
    $('.subtotal .cost').text(subTotalCostFR + ' €');
    var taxRate = parseFloat($('.tax .price').text()
      .replace(',', '.').replace(/[^\d\.]/g, '')) / 100;
    var tax = Math.ceil(totalCost * taxRate * 100) / 100;
    var taxFR = tax.toFixed(2).replace('.', ',');
    $('.tax .cost').text(taxFR + ' €');
    totalCost += tax;
    $('.shipping .quantity').text(String(totalQuantity));
    var shippingRate = parseFloat($('.shipping .price').text()
      .replace(',', '.').replace(/[^\d\.]/g, ''));
    var shipping = totalQuantity * shippingRate;
    var shippingFR = shipping.toFixed(2).replace('.', ',');
    $('.shipping .cost').text(shippingFR + ' €');
    totalCost += shipping;
    var totalCostFR = totalCost.toFixed(2).replace('.', ',');
    $('.total .cost').text(totalCostFR + ' €');	
  });

  $('<th>&nbsp;</th>')
  .insertAfter('#cart thead th:nth-child(2)');
  $('#cart tbody tr').each(function() {
     $deleteButton = $('<img />').attr({
       'width': '16',
       'height': '16',
       'src': '../images/cross.png',
       'alt': 'retirer du panier',
       'title': 'retirer du panier',
       'class': 'clickable'
     }).click(function() {
       $(this).parents('tr').find('td.quantity input')
         .val(0).trigger('change')
       .end().hide();
       stripe();
     });
     $('<td></td>')
    .insertAfter($('td:nth-child(2)', this))
    .append($deleteButton);
   });
  $('<td>&nbsp;</td>')
  .insertAfter('#cart tfoot td:nth-child(2)');
});

/***************************************
   Modifier les informations de livraison.
-------------------------------------- */

$(document).ready(function() {
  var editShipping = function() {
    $.get('shipping.php', function(data) {
      $('#shipping-name').remove();
      $(data).hide().appendTo('#shipping').slideDown();
      $('#shipping form').submit(saveShipping);
    });
    return false;
  };
  var saveShipping = function() {
    var postData = $(this).serialize() + '&op=Save';
    
    $.post('shipping.php', postData, function(data) {
      $('#shipping form').remove();
      $(data).appendTo('#shipping');
      $('#shipping-name').click(editShipping);
    });
    return false;
  };
  $('#shipping-name').click(editShipping);
});

