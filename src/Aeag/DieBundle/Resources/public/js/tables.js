// Exemple : groupes de lignes.
// $(document).ready(function() {
//   $('table.sortable tbody tr:odd').addClass('odd');
//   $('table.sortable tbody tr:even').addClass('even');
// });

// Exemple : tri alphabétique simple.
// Étape 1
// $(document).ready(function() {
//   $('table.sortable').each(function() {
//     var $table = $(this);
//     $('th', $table).each(function(column) {
//       var $header = $(this);
//       if ($header.is('.sort-alpha')) {
//         $header.addClass('clickable').hover(function() {
//           $header.addClass('hover');
//         }, function() {
//           $header.removeClass('hover');
//         }).click(function() {
//           var rows = $table.find('tbody > tr').get();
//           rows.sort(function(a, b) {
//             var keyA = $(a).children('td').eq(column).text()
//               .toUpperCase();
//             var keyB = $(b).children('td').eq(column).text()
//               .toUpperCase();
//             if (keyA < keyB) return -1;
//             if (keyA > keyB) return 1;
//             return 0;
//           });
//           $.each(rows, function(index, row) {
//             $table.children('tbody').append(row);
//           });
//         });
//       }
//     });
//   });
// });

// Étape 2
// $(document).ready(function() {
//   var alternateRowColors = function($table) {
//     $('tbody tr:odd', $table)
//       .removeClass('even').addClass('odd');
//     $('tbody tr:even', $table)
//       .removeClass('odd').addClass('even');
//   };
//   
//   $('table.sortable').each(function() {
//     var $table = $(this);
//     alternateRowColors($table);
//     $('th', $table).each(function(column) {
//       var $header = $(this);
//       if ($header.is('.sort-alpha')) {
//         $header.addClass('clickable').hover(function() {
//           $header.addClass('hover');
//         }, function() {
//           $header.removeClass('hover');
//         }).click(function() {
//           var rows = $table.find('tbody > tr').get();
//           rows.sort(function(a, b) {
//             var keyA = $(a).children('td').eq(column).text()
//               .toUpperCase();
//             var keyB = $(b).children('td').eq(column).text()
//               .toUpperCase();
//             if (keyA < keyB) return -1;
//             if (keyA > keyB) return 1;
//             return 0;
//           });
//           $.each(rows, function(index, row) {
//             $table.children('tbody').append(row);
//           });
//           alternateRowColors($table);
//         });
//       }
//     });
//   });
// });

// Exemple : code d'application des bandes avec la méthode des plugins.
// jQuery.fn.alternateRowColors = function() {
//   $('tbody tr:odd', this)
//     .removeClass('even').addClass('odd');
//   $('tbody tr:even', this)
//     .removeClass('odd').addClass('even');
//   return this;
// };
// 
// $(document).ready(function() {
//   $('table.sortable').each(function() {
//     var $table = $(this);
//     $table.alternateRowColors();
//     $('th', $table).each(function(column) {
//       var $header = $(this);
//       if ($header.is('.sort-alpha')) {
//         $header.addClass('clickable').hover(function() {
//           $header.addClass('hover');
//         }, function() {
//           $header.removeClass('hover');
//         }).click(function() {
//           var rows = $table.find('tbody > tr').get();
//           rows.sort(function(a, b) {
//             var keyA = $(a).children('td').eq(column).text()
//               .toUpperCase();
//             var keyB = $(b).children('td').eq(column).text()
//               .toUpperCase();
//             if (keyA < keyB) return -1;
//             if (keyA > keyB) return 1;
//             return 0;
//           });
//           $.each(rows, function(index, row) {
//             $table.children('tbody').append(row);
//           });
//           $table.alternateRowColors();
//         });
//       }
//     });
//   });
// });

// Exemple : améliorer le tri avec les propriétés expando.
// jQuery.fn.alternateRowColors = function() {
//   $('tbody tr:odd', this)
//     .removeClass('even').addClass('odd');
//   $('tbody tr:even', this)
//     .removeClass('odd').addClass('even');
//   return this;
// };
// 
// $(document).ready(function() {
//   $('table.sortable').each(function() {
//     var $table = $(this);
//     $table.alternateRowColors();
//     $('th', $table).each(function(column) {
//       var $header = $(this);
//       if ($header.is('.sort-alpha')) {
//         $header.addClass('clickable').hover(function() {
//           $header.addClass('hover');
//         }, function() {
//           $header.removeClass('hover');
//         }).click(function() {
//           var rows = $table.find('tbody > tr').get();
//           $.each(rows, function(index, row) {
//             row.sortKey = $(row).children('td').eq(column)
//               .text().toUpperCase();
//           });
//           rows.sort(function(a, b) {
//             if (a.sortKey < b.sortKey) return -1;
//             if (a.sortKey > b.sortKey) return 1;
//             return 0;
//           });
//           $.each(rows, function(index, row) {
//             $table.children('tbody').append(row);
//             row.sortKey = null;
//           });
//           $table.alternateRowColors();
//         });
//       }
//     });
//   });
// });

// Exemple : marquage explicite des clés de tri.
// jQuery.fn.alternateRowColors = function() {
//   $('tbody tr:odd', this)
//     .removeClass('even').addClass('odd');
//   $('tbody tr:even', this)
//     .removeClass('odd').addClass('even');
//   return this;
// };
// 
// $(document).ready(function() {
//   $('table.sortable').each(function() {
//     var $table = $(this);
//     $table.alternateRowColors();
//     $('th', $table).each(function(column) {
//       var $header = $(this);
//       if ($header.is('.sort-alpha')) {
//         $header.addClass('clickable').hover(function() {
//           $header.addClass('hover');
//         }, function() {
//           $header.removeClass('hover');
//         }).click(function() {
//           var rows = $table.find('tbody > tr').get();
//           $.each(rows, function(index, row) {
//             var $cell = $(row).children('td').eq(column);
//             row.sortKey = $cell.find('.sort-key').text()
//               .toUpperCase() + ' '
//               + $cell.text().toUpperCase();
//           });
//           rows.sort(function(a, b) {
//             if (a.sortKey < b.sortKey) return -1;
//             if (a.sortKey > b.sortKey) return 1;
//             return 0;
//           });
//           $.each(rows, function(index, row) {
//             $table.children('tbody').append(row);
//             row.sortKey = null;
//           });
//           $table.alternateRowColors();
//         });
//       }
//     });
//   });
// });

// Exemple : trier d'autres types de données.
// jQuery.fn.alternateRowColors = function() {
//   $('tbody tr:odd', this)
//     .removeClass('even').addClass('odd');
//   $('tbody tr:even', this)
//     .removeClass('odd').addClass('even');
//   return this;
// };
// 
// $(document).ready(function() {
//   $('table.sortable').each(function() {
//     var $table = $(this);
//     $table.alternateRowColors();
//     $('th', $table).each(function(column) {
//       var $header = $(this);
//       var findSortKey;
//       if ($header.is('.sort-alpha')) {
//         findSortKey = function($cell) {
//           return $cell.find('.sort-key').text().toUpperCase()
//             + ' ' + $cell.text().toUpperCase();
//         };
//       }
//       else if ($header.is('.sort-numeric')) {
//         findSortKey = function($cell) {
//           var key = $cell.text().replace(/[\D,]/g, '');
//           key = parseFloat(key);
//           return isNaN(key) ? 0 : key;
//         };
//       }
//       else if ($header.is('.sort-date')) {
//         findSortKey = function($cell) {
//           var monthFRtoEN = {
//             'janv': 'Jan', 'févr': 'Feb', 'mars': 'Mar', 'avr': 'Apr',
//             'mai': 'May',  'juin': 'Jun', 'juil': 'Jul', 'août': 'Aug',
//             'sept': 'Sep', 'oct': 'Oct',  'nov': 'Nov',  'déc': 'Dec'
//           };
//           var monthFR = $cell.text().replace(/[^a-zéû]/gi, '').toLowerCase();
//           var dateEN = $cell.text().replace(monthFR, monthFRtoEN[monthFR]);
//           return Date.parse('1 ' + dateEN);
//         };
//       }
//           
//       if (findSortKey) {
//         $header.addClass('clickable').hover(function() {
//           $header.addClass('hover');
//         }, function() {
//           $header.removeClass('hover');
//         }).click(function() {
//           var rows = $table.find('tbody > tr').get();
//           $.each(rows, function(index, row) {
//             var $cell = $(row).children('td').eq(column);
//             row.sortKey = findSortKey($cell);
//           });
//           rows.sort(function(a, b) {
//             if (a.sortKey < b.sortKey) return -1;
//             if (a.sortKey > b.sortKey) return 1;
//             return 0;
//           });
//           $.each(rows, function(index, row) {
//             $table.children('tbody').append(row);
//             row.sortKey = null;
//           });
//           $table.alternateRowColors();
//         });
//       }
//     });
//   });
// });

// Exemple : mettre en exergue une colonne.
// jQuery.fn.alternateRowColors = function() {
//   $('tbody tr:odd', this)
//     .removeClass('even').addClass('odd');
//   $('tbody tr:even', this)
//     .removeClass('odd').addClass('even');
//   return this;
// };
// 
// $(document).ready(function() {
//   $('table.sortable').each(function() {
//     var $table = $(this);
//     $table.alternateRowColors();
//     $('th', $table).each(function(column) {
//       var $header = $(this);
//       var findSortKey;
//       if ($header.is('.sort-alpha')) {
//         findSortKey = function($cell) {
//           return $cell.find('.sort-key').text().toUpperCase()
//             + ' ' + $cell.text().toUpperCase();
//         };
//       }
//       else if ($header.is('.sort-numeric')) {
//         findSortKey = function($cell) {
//           var key = $cell.text().replace(/[\D,]/g, '');
//           key = parseFloat(key);
//           return isNaN(key) ? 0 : key;
//         };
//       }
//       else if ($header.is('.sort-date')) {
//         findSortKey = function($cell) {
//           var monthFRtoEN = {
//             'janv': 'Jan', 'févr': 'Feb', 'mars': 'Mar', 'avr': 'Apr',
//             'mai': 'May',  'juin': 'Jun', 'juil': 'Jul', 'août': 'Aug',
//             'sept': 'Sep', 'oct': 'Oct',  'nov': 'Nov',  'déc': 'Dec'
//           };
//           var monthFR = $cell.text().replace(/[^a-zéû]/gi, '').toLowerCase();
//           var dateEN = $cell.text().replace(monthFR, monthFRtoEN[monthFR]);
//           return Date.parse('1 ' + dateEN);
//         };
//       }
//           
//       if (findSortKey) {
//         $header.addClass('clickable').hover(function() {
//           $header.addClass('hover');
//         }, function() {
//           $header.removeClass('hover');
//         }).click(function() {
//           var rows = $table.find('tbody > tr').get();
//           $.each(rows, function(index, row) {
//             var $cell = $(row).children('td').eq(column);
//             row.sortKey = findSortKey($cell);
//           });
//           rows.sort(function(a, b) {
//             if (a.sortKey < b.sortKey) return -1;
//             if (a.sortKey > b.sortKey) return 1;
//             return 0;
//           });
//           $.each(rows, function(index, row) {
//             $table.children('tbody').append(row);
//             row.sortKey = null;
//           });
//           $table.find('td').removeClass('sorted')
//             .filter(':nth-child(' + (column + 1) + ')')
//             .addClass('sorted');
//           $table.alternateRowColors();
//         });
//       }
//     });
//   });
// });

// Exemple : inverser le sens du tri.
// jQuery.fn.alternateRowColors = function() {
//   $('tbody tr:odd', this)
//     .removeClass('even').addClass('odd');
//   $('tbody tr:even', this)
//     .removeClass('odd').addClass('even');
//   return this;
// };
// 
// $(document).ready(function() {
//   $('table.sortable').each(function() {
//     var $table = $(this);
//     $table.alternateRowColors();
//     $('th', $table).each(function(column) {
//       var $header = $(this);
//       var findSortKey;
//       if ($header.is('.sort-alpha')) {
//         findSortKey = function($cell) {
//           return $cell.find('.sort-key').text().toUpperCase()
//             + ' ' + $cell.text().toUpperCase();
//         };
//       }
//       else if ($header.is('.sort-numeric')) {
//         findSortKey = function($cell) {
//           var key = $cell.text().replace(/[\D,]/g, '');
//           key = parseFloat(key);
//           return isNaN(key) ? 0 : key;
//         };
//       }
//       else if ($header.is('.sort-date')) {
//         findSortKey = function($cell) {
//           var monthFRtoEN = {
//             'janv': 'Jan', 'févr': 'Feb', 'mars': 'Mar', 'avr': 'Apr',
//             'mai': 'May',  'juin': 'Jun', 'juil': 'Jul', 'août': 'Aug',
//             'sept': 'Sep', 'oct': 'Oct',  'nov': 'Nov',  'déc': 'Dec'
//           };
//           var monthFR = $cell.text().replace(/[^a-zéû]/gi, '').toLowerCase();
//           var dateEN = $cell.text().replace(monthFR, monthFRtoEN[monthFR]);
//           return Date.parse('1 ' + dateEN);
//         };
//       }
//           
//       if (findSortKey) {
//         $header.addClass('clickable').hover(function() {
//           $header.addClass('hover');
//         }, function() {
//           $header.removeClass('hover');
//         }).click(function() {
//           var sortDirection = 1;
//           if ($header.is('.sorted-asc')) {
//             sortDirection = -1;
//           }
//           var rows = $table.find('tbody > tr').get();
//           $.each(rows, function(index, row) {
//             var $cell = $(row).children('td').eq(column);
//             row.sortKey = findSortKey($cell);
//           });
//           rows.sort(function(a, b) {
//             if (a.sortKey < b.sortKey) return -sortDirection;
//             if (a.sortKey > b.sortKey) return sortDirection;
//             return 0;
//           });
//           $.each(rows, function(index, row) {
//             $table.children('tbody').append(row);
//             row.sortKey = null;
//           });
//           $table.find('th').removeClass('sorted-asc')
//             .removeClass('sorted-desc');
//           if (sortDirection == 1) {
//             $header.addClass('sorted-asc');
//           }
//           else {
//             $header.addClass('sorted-desc');
//           }
//           $table.find('td').removeClass('sorted')
//             .filter(':nth-child(' + (column + 1) + ')')
//             .addClass('sorted');
//           $table.alternateRowColors();
//         });
//       }
//     });
//   });
// });


// Exemple : pagination en JavaScript.
// Étape 1
// $(document).ready(function() {
//   $('table.paginated').each(function() {
//     var currentPage = 0;
//     var numPerPage = 10;
//     var $table = $(this);
//     
//     $table.find('tbody tr').hide()
//       .slice(currentPage * numPerPage,
//         (currentPage + 1) * numPerPage)
//       .show();
//   });
// });

// Étape 2
// $(document).ready(function() {
//   $('table.paginated').each(function() {
//     var currentPage = 0;
//     var numPerPage = 10;
//     var $table = $(this);
//     
//     var numRows = $table.find('tbody tr').length;
//     var numPages = Math.ceil(numRows / numPerPage);
// 
//     var $pager = $('<div class="pager"></div>');
//     for (var page = 0; page < numPages; page++) {
//       $('<span class="page-number">' + (page + 1) + '</span>')
//         .appendTo($pager).addClass('clickable');
//     }
//     $pager.insertBefore($table);
//     
//     $table.find('tbody tr').hide()
//       .slice(currentPage * numPerPage,
//         (currentPage + 1) * numPerPage)
//       .show();
//   });
// });

// Étape 3
// $(document).ready(function() {
//   $('table.paginated').each(function() {
//     var currentPage = 0;
//     var numPerPage = 10;
//     var $table = $(this);
//     var repaginate = function() {
//       $table.find('tbody tr').hide()
//         .slice(currentPage * numPerPage,
//           (currentPage + 1) * numPerPage)
//         .show();
//     };
//     var numRows = $table.find('tbody tr').length;
//     var numPages = Math.ceil(numRows / numPerPage);
//     var $pager = $('<div class="pager"></div>');
//     for (var page = 0; page < numPages; page++) {
//       $('<span class="page-number"></span>').text(page + 1)
//         .click(function() {
//           currentPage = page;
//           repaginate();
//         }).appendTo($pager).addClass('clickable');
//     }
//     $pager.insertBefore($table);
//   });
// });

// Étape 4
// $(document).ready(function() {
//   $('table.paginated').each(function() {
//     var currentPage = 0;
//     var numPerPage = 10;
//     var $table = $(this);
//     var repaginate = function() {
//       $table.find('tbody tr').hide()
//         .slice(currentPage * numPerPage,
//           (currentPage + 1) * numPerPage)
//         .show();
//     };
//     var numRows = $table.find('tbody tr').length;
//     var numPages = Math.ceil(numRows / numPerPage);
//     var $pager = $('<div class="pager"></div>');
//     for (var page = 0; page < numPages; page++) {
//       $('<span class="page-number"></span>').text(page + 1)
//         .bind('click', {newPage: page}, function(event) {
//           currentPage = event.data['newPage'];
//           repaginate();
//         }).appendTo($pager).addClass('clickable');
//     }
//     $pager.insertBefore($table);
//   });
// });

// Étape 5
// $(document).ready(function() {
//   $('table.paginated').each(function() {
//     var currentPage = 0;
//     var numPerPage = 10;
//     var $table = $(this);
//     var repaginate = function() {
//       $table.find('tbody tr').hide()
//         .slice(currentPage * numPerPage,
//           (currentPage + 1) * numPerPage)
//         .show();
//     };
//     var numRows = $table.find('tbody tr').length;
//     var numPages = Math.ceil(numRows / numPerPage);
//     var $pager = $('<div class="pager"></div>');
//     for (var page = 0; page < numPages; page++) {
//       $('<span class="page-number"></span>').text(page + 1)
//         .bind('click', {newPage: page}, function(event) {
//           currentPage = event.data['newPage'];
//           repaginate();
//           $(this).addClass('active')
//             .siblings().removeClass('active');
//         }).appendTo($pager).addClass('clickable');
//     }
//     $pager.insertBefore($table)
//       .find('span.page-number:first').addClass('active');
//   });
// });


// Version finale du code : tri et pagination

jQuery.fn.alternateRowColors = function() {
  $('tbody tr:odd', this)
    .removeClass('even').addClass('odd');
  $('tbody tr:even', this)
    .removeClass('odd').addClass('even');
  return this;
};

$(document).ready(function() {
  $('table.sortable').each(function() {
    var $table = $(this);
    $table.alternateRowColors();
    $('th', $table).each(function(column) {
      var $header = $(this);
      var findSortKey;
      if ($header.is('.sort-alpha')) {
        findSortKey = function($cell) {
          return $cell.find('.sort-key').text().toUpperCase()
            + ' ' + $cell.text().toUpperCase();
        };
      }
      else if ($header.is('.sort-numeric')) {
        findSortKey = function($cell) {
          var key = $cell.text().replace(/[\D,]/g, '');
          key = parseFloat(key);
          return isNaN(key) ? 0 : key;
        };
      }
      else if ($header.is('.sort-date')) {
        findSortKey = function($cell) {
          var monthFRtoEN = {
            'janv': 'Jan', 'févr': 'Feb', 'mars': 'Mar', 'avr': 'Apr',
            'mai': 'May',  'juin': 'Jun', 'juil': 'Jul', 'août': 'Aug',
            'sept': 'Sep', 'oct': 'Oct',  'nov': 'Nov',  'déc': 'Dec'
          };
          var monthFR = $cell.text().replace(/[^a-zéû]/gi, '');
          var dateEN = $cell.text().replace(monthFR, monthFRtoEN[monthFR.toLowerCase()]);
          return Date.parse('1 ' + dateEN);
        };
      }	  
          
      if (findSortKey) {
        $header.addClass('clickable').hover(function() {
          $header.addClass('hover');
        }, function() {
          $header.removeClass('hover');
        }).click(function() {
          var sortDirection = 1;
          if ($header.is('.sorted-asc')) {
            sortDirection = -1;
          }
          var rows = $table.find('tbody > tr').get();
          $.each(rows, function(index, row) {
            var $cell = $(row).children('td').eq(column);
            row.sortKey = findSortKey($cell);
          });
          rows.sort(function(a, b) {
            if (a.sortKey < b.sortKey) return -sortDirection;
            if (a.sortKey > b.sortKey) return sortDirection;
            return 0;
          });
          $.each(rows, function(index, row) {
            $table.children('tbody').append(row);
            row.sortKey = null;
          });
          $table.find('th').removeClass('sorted-asc')
            .removeClass('sorted-desc');
          if (sortDirection == 1) {
            $header.addClass('sorted-asc');
          }
          else {
            $header.addClass('sorted-desc');
          }
          $table.find('td').removeClass('sorted')
            .filter(':nth-child(' + (column + 1) + ')')
            .addClass('sorted');
          $table.alternateRowColors();
          $table.trigger('repaginate');
        });
      }
    });
  });
});

$(document).ready(function() {
  $('table.paginated').each(function() {
    var currentPage = 0;
    var numPerPage = 10;
    var $table = $(this);
    $table.bind('repaginate', function() {
      $table.find('tbody tr').hide()
        .slice(currentPage * numPerPage,
          (currentPage + 1) * numPerPage)
        .show();
    });
    var numRows = $table.find('tbody tr').length;
    var numPages = Math.ceil(numRows / numPerPage);
    var $pager = $('<div class="pager"></div>');
    for (var page = 0; page < numPages; page++) {
      $('<span class="page-number"></span>').text(page + 1)
        .bind('click', {newPage: page}, function(event) {
          currentPage = event.data['newPage'];
          $table.trigger('repaginate');
          $(this).addClass('active')
            .siblings().removeClass('active');
        }).appendTo($pager).addClass('clickable');
    }
    $pager.insertBefore($table)
      .find('span.page-number:first').addClass('active');
  });
});


// Exemple : effet de bande.
// Étape 1
// $(document).ready(function() {
//   $('table.striped tr:odd').addClass('odd');
//   $('table.striped tr:even').addClass('even');
// });

// Étape 2
// $(document).ready(function() {
//   $('table.striped tr:nth-child(odd)').addClass('odd');
//   $('table.striped tr:nth-child(even)').addClass('even');
// });

// Étape 3
// $(document).ready(function() {
//   $('table.striped tr:not(:has(th)):odd').addClass('odd');
//   $('table.striped tr:not(:has(th)):even').addClass('even');
// });

// Étape 4
// $(document).ready(function() {
//   $('table.striped tbody').each(function() {
//     $(this).find('tr:not(:has(th)):odd').addClass('odd');
//     $(this).find('tr:not(:has(th)):even').addClass('even');
//   });
// });

// Exemple : effet de bande élaboré.
// Étape 1
// $(document).ready(function() {
//   $('table.striped tbody').each(function() {
//     $(this).find('tr:not(:has(th))').filter(function(index) {
//       return (index % 6) < 3;
//     }).addClass('odd');
//   });
// });

// Étape 2
// $(document).ready(function() {
//   $('table.striped tbody').each(function() {
//     $(this).find('tr:not(:has(th))').addClass('even')
//     .filter(function(index) {
//       return (index % 6) < 3;
//     }).removeClass('even').addClass('odd');
//   });
// });

// Exemple : mise en exergue interactive.
// Étape 1
// $(document).ready(function() {
//   var $authorCells = $('table.striped td:nth-child(3)');
//   $authorCells.click(function() {
//     // Effectuer la mise en exergue.
//   });
// });

// Étape 2
// $(document).ready(function() {
//   var $authorCells = $('table.striped td:nth-child(3)');
//   $authorCells.click(function() {
//     var authorName = $(this).text();
//     $authorCells.each(function(index) {
//       if (authorName == $(this).text()) {
//         $(this).parent().toggleClass('highlight');
//       };
//     });
//   });
// });

// Étape 3
// $(document).ready(function() {
//   var $authorCells = $('table.striped td:nth-child(3)');
//   $authorCells.click(function() {
//     var authorName = $(this).text();
//     $authorCells.each(function(index) {
//       if (authorName == $(this).text()) {
//         $(this).parent().toggleClass('highlight');
//       }
//       else {
//         $(this).parent().removeClass('highlight');
//       }
//     });
//   });
// });

// Exemple : info-bulles.
// Étape 1, highlight author rows
// $(document).ready(function() {
//   var $authorCells = $('table.striped td:nth-child(3)');
//   $authorCells
//     .addClass('clickable')
//     .click(function() {
//       var authorName = $(this).text();
//       $authorCells.each(function(index) {
//         if (authorName == $(this).text()) {
//           $(this).parent().toggleClass('highlight');
//         }
//         else {
//           $(this).parent().removeClass('highlight');
//         }
//       });
//     });
// });

// Étape 2
// $(document).ready(function() {
//   var $authorCells = $('table.striped td:nth-child(3)');
//   var $tooltip = $('<div id="tooltip"></div>').appendTo('body');
//   
//   var positionTooltip = function(event) {
//     var tPosX = event.pageX;
//     var tPosY = event.pageY + 20;
//     $tooltip.css({top: tPosY, left: tPosX});
//   };
//   var showTooltip = function(event) {
//     var authorName = $(this).text();
//     $tooltip
//       .text('Mettre en exergue tous les articles rédigés par ' + authorName)
//       .show();
//     positionTooltip(event);
//   };
//   var hideTooltip = function() {
//     $tooltip.hide();
//   };
// 
//   $authorCells
//     .addClass('clickable')
//     .hover(showTooltip, hideTooltip)
//     .mousemove(positionTooltip)
//     .click(function(event) {
//       var authorName = $(this).text();
//       $authorCells.each(function(index) {
//         if (authorName == $(this).text()) {
//           $(this).parent().toggleClass('highlight');
//         }
//         else {
//           $(this).parent().removeClass('highlight');
//         }
//       });
//     });
// });

// Étape 3
// $(document).ready(function() {
//   var $authorCells = $('table.striped td:nth-child(3)');
//   var $tooltip = $('<div id="tooltip"></div>').appendTo('body');
//   
//   var positionTooltip = function(event) {
//     var tPosX = event.pageX;
//     var tPosY = event.pageY + 20;
//     $tooltip.css({top: tPosY, left: tPosX});
//   };
//   var showTooltip = function(event) {
//     var authorName = $(this).text();
//     var action = 'Mettre en exergue';
//     if ($(this).parent().is('.highlight')) {
//       action = 'Ne plus mettre en exergue';
//     }
//     $tooltip
//       .text(action + ' tous les articles rédigés par ' + authorName)
//       .show();
//     positionTooltip(event);
//   };
//   var hideTooltip = function() {
//     $tooltip.hide();
//   };
// 
//   $authorCells
//     .addClass('clickable')
//     .hover(showTooltip, hideTooltip)
//     .mousemove(positionTooltip)
//     .click(function(event) {
//       var authorName = $(this).text();
//       $authorCells.each(function(index) {
//         if (authorName == $(this).text()) {
//           $(this).parent().toggleClass('highlight');
//         }
//         else {
//           $(this).parent().removeClass('highlight');
//         }
//       });
//       showTooltip.call(this, event);
//     });
// });

// Exemple : réduire et développer des sections.
// $(document).ready(function() {
//   var collapseIcon = '../images/bullet_toggle_minus.png';
//   var collapseText = 'Réduire cette section';
//   var expandIcon = '../images/bullet_toggle_plus.png';
//   var expandText = 'Développer cette section';
//   $('table.collapsible tbody').each(function() {
//     var $section = $(this);
//     $('<img />').attr('src', collapseIcon)
//       .attr('alt', collapseText)
//       .prependTo($section.find('th'))
//       .addClass('clickable')
//       .click(function() {
//         if ($section.is('.collapsed')) {
//           $section.removeClass('collapsed')
//             .find('tr:not(:has(th))').fadeIn('slow');
//           $(this).attr('src', collapseIcon)
//             .attr('alt', collapseText);
//         }
//         else {
//           $section.addClass('collapsed')
//             .find('tr:not(:has(th))').fadeOut('fast');
//           $(this).attr('src', expandIcon)
//             .attr('alt', expandText);
//         }
//       });
//   });
// });

// Exemple : filtrage.
// Étape 1
// $(document).ready(function() {
//   $('table.filterable').each(function() {
//     var $table = $(this);
// 
//     $table.find('th').each(function(column) {
//       if ($(this).is('.filter-column')) {
//         var $filters = $('<div class="filters"></div>');
//         $('<h3></h3>')
//           .text('Filtrer par ' + $(this).text() + ' :')
//           .appendTo($filters);
//         $filters.insertBefore($table);
//       }
//     });
//   });
// });

// Étape 2
// $(document).ready(function() {
//   $('table.filterable').each(function() {
//     var $table = $(this);
// 
//     $table.find('th').each(function(column) {
//       if ($(this).is('.filter-column')) {
//         var $filters = $('<div class="filters"></div>');
//         $('<h3></h3>')
//           .text('Filtrer par ' + $(this).text() + ' :')
//           .appendTo($filters);
//           
//         var keywords = ['conférence', 'sortie'];
//         $.each(keywords, function(index, keyword) {
//           $('<div class="filter"></div>').text(keyword)
//             .bind('click', {key: keyword}, function(event) {
//               $('tr:not(:has(th))', $table).each(function() {
//                 var value = $('td', this).eq(column).text();
//                 if (value == event.data['key']) {
//                   $(this).show();
//                 }
//                 else {
//                   $(this).hide();
//                 }
//               });
//               $(this).addClass('active')
//                 .siblings().removeClass('active');
//             }).addClass('clickable').appendTo($filters);
//         });
//       
//         $filters.insertBefore($table);
//       }
//     });
//   });
// });

// Étape 3
// $(document).ready(function() {
//   $('table.filterable').each(function() {
//     var $table = $(this);
// 
//     $table.find('th').each(function(column) {
//       if ($(this).is('.filter-column')) {
//         var $filters = $('<div class="filters"></div>');
//         $('<h3></h3>')
//           .text('Filtrer par ' + $(this).text() + ' :')
//           .appendTo($filters);
//         
//         $('<div class="filter">Tous</div>').click(function() {
//           $table.find('tbody tr').show();
//           $(this).addClass('active')
//             .siblings().removeClass('active');
//         }).addClass('clickable active').appendTo($filters);
// 
//         var keywords = {};
//         $table.find('td:nth-child(' + (column + 1) + ')')
//           .each(function() {
//             keywords[$(this).text()] = $(this).text();
//           });        
// 
//         $.each(keywords, function(index, keyword) {
//           $('<div class="filter"></div>').text(keyword)
//             .bind('click', {key: keyword}, function(event) {
//               $('tr:not(:has(th))', $table).each(function() {
//                 var value = $('td', this).eq(column).text();
//                 if (value == event.data['key']) {
//                   $(this).show();
//                 }
//                 else {
//                   $(this).hide();
//                 }
//               });
//               $(this).addClass('active')
//                 .siblings().removeClass('active');
//             }).addClass('clickable').appendTo($filters);
//         });
//       
//         $filters.insertBefore($table);
//       }
//     });
//   });
// });

// Étape 4
// $(document).ready(function() {
//   $('table.striped').bind('stripe', function() {
//     $('tbody', this).each(function() {
//       $(this).find('tr:visible:not(:has(th))')
//         .removeClass('odd').addClass('even')
//         .filter(function(index) {
//           return (index % 6) < 3;
//         }).removeClass('even').addClass('odd');
//     });
//   }).trigger('stripe');
// });

// $(document).ready(function() {
//   $('table.filterable').each(function() {
//     var $table = $(this);
// 
//     $table.find('th').each(function(column) {
//       if ($(this).is('.filter-column')) {
//         var $filters = $('<div class="filters"></div>');
//         $('<h3></h3>')
//           .text('Filtrer par ' + $(this).text() + ' :')
//           .appendTo($filters);
//         
//         $('<div class="filter">Tous</div>').click(function() {
//           $table.find('tbody tr').show();
//           $(this).addClass('active')
//             .siblings().removeClass('active');
//           $table.trigger('stripe');
//         }).addClass('clickable active').appendTo($filters);
// 
//         var keywords = {};
//         $table.find('td:nth-child(' + (column + 1) + ')')
//           .each(function() {
//             keywords[$(this).text()] = $(this).text();
//           });        
// 
//         $.each(keywords, function(index, keyword) {
//           $('<div class="filter"></div>').text(keyword)
//             .bind('click', {key: keyword}, function(event) {
//               $('tr:not(:has(th))', $table).each(function() {
//                 var value = $('td', this).eq(column).text();
//                 if (value == event.data['key']) {
//                   $(this).show();
//                 }
//                 else {
//                   $(this).hide();
//                 }
//               });
//               $(this).addClass('active')
//                 .siblings().removeClass('active');
//               $table.trigger('stripe');
//             }).addClass('clickable').appendTo($filters);
//         });
//       
//         $filters.insertBefore($table);
//       }
//     });
//   });
// });


// Version fiale du code : mise en exergue, info-bulles, réduction et filtrage.

/* 
 * IMPORTANT :
 * À partir de jQuery 1.3.2, la visibilité d'un élément est détectée différemment.
 * Par conséquent, la ligne :
      $(this).find('tr:visible:not(:has(th))')
 * est remplacée par : 
     $(this).find('tr:not(:has(th))')
       .filter(function() {
         return this.style.display != 'none';
       })
 * 
 */

$(document).ready(function() {
  $('table.striped').bind('stripe', function() {
    $('tbody', this).each(function() {
      $(this).find('tr:not(:has(th))')
        .filter(function() {
          return this.style.display != 'none';
        })
        .removeClass('odd').addClass('even')
        .filter(function(index) {
          return (index % 6) < 3;
        }).removeClass('even').addClass('odd');
    });
  }).trigger('stripe');
});

$(document).ready(function() {
  var $authorCells = $('table.striped td:nth-child(3)');
  var $tooltip = $('<div id="tooltip"></div>').appendTo('body');
  
  var positionTooltip = function(event) {
    var tPosX = event.pageX;
    var tPosY = event.pageY + 20;
    $tooltip.css({top: tPosY, left: tPosX});
  };
  var showTooltip = function(event) {
    var authorName = $(this).text();
    var action = 'Mettre en exergue';
    if ($(this).parent().is('.highlight')) {
      action = 'Ne plus mettre en exergue';
    }
    $tooltip
      .text(action + ' tous les articles rédigés par ' + authorName)
      .show();
    positionTooltip(event);
  };
  var hideTooltip = function() {
    $tooltip.hide();
  };

  $authorCells
    .addClass('clickable')
    .hover(showTooltip, hideTooltip)
    .mousemove(positionTooltip)
    .click(function(event) {
      var authorName = $(this).text();
      $authorCells.each(function(index) {
        if (authorName == $(this).text()) {
          $(this).parent().toggleClass('highlight');
        }
        else {
          $(this).parent().removeClass('highlight');
        }
      });
      showTooltip.call(this, event);
    });
});

$(document).ready(function() {
  var collapseIcon = '../images/bullet_toggle_minus.png';
  var collapseText = 'Réduire cette section';
  var expandIcon = '../images/bullet_toggle_plus.png';
  var expandText = 'Développer cette section';
  $('table.collapsible tbody').each(function() {
    var $section = $(this);
    $('<img />').attr('src', collapseIcon)
      .attr('alt', collapseText)
      .prependTo($section.find('th'))
      .addClass('clickable')
      .click(function() {
        if ($section.is('.collapsed')) {
          $section.removeClass('collapsed')
            .find('tr:not(:has(th)):not(.filtered)')
            .fadeIn('fast');
          $(this).attr('src', collapseIcon)
            .attr('alt', collapseText);
        }
        else {
          $section.addClass('collapsed')
            .find('tr:not(:has(th))')
            .fadeOut('fast', function() {
              $(this).css('display', 'none');
            });
          $(this).attr('src', expandIcon)
            .attr('alt', expandText);
        }
        $section.parent().trigger('stripe');
      });
  });
});

$(document).ready(function() {
  $('table.filterable').each(function() {
    var $table = $(this);

    $table.find('th').each(function(column) {
      if ($(this).is('.filter-column')) {
        var $filters = $('<div class="filters"></div>');
        $('<h3></h3>')
          .text('Filtrer par ' + $(this).text() + ' :')
          .appendTo($filters);
        
        $('<div class="filter">Tous</div>').click(function() {
          $table.find('tbody tr').removeClass('filtered');
          $(this).addClass('active')
            .siblings().removeClass('active');
          $table.trigger('stripe');
        }).addClass('clickable active').appendTo($filters);

        var keywords = {};
        $table.find('td:nth-child(' + (column + 1) + ')')
          .each(function() {
            keywords[$(this).text()] = $(this).text();
          });        

        $.each(keywords, function(index, keyword) {
          $('<div class="filter"></div>').text(keyword)
            .bind('click', {key: keyword}, function(event) {
              $('tr:not(:has(th))', $table).each(function() {
                var value = $('td', this).eq(column).text();
                if (value == event.data['key']) {
                  $(this).removeClass('filtered');
                }
                else {
                  $(this).addClass('filtered');
                }
              });
              $(this).addClass('active')
                .siblings().removeClass('active');
              $table.trigger('stripe');
            }).addClass('clickable').appendTo($filters);
        });
      
        $filters.insertBefore($table);
      }
    });
  });
});
