function recalcRadio(z)
{
  jQuery('.radioname').each(function(i, e)
      {
        jQuery(e).val('name_' + i);

        if(z != undefined && i == z) jQuery(e).prop('checked', true);
      });
}

function adjustLayout()
{
  var f = jQuery('#wpfx_form :selected').val();

  switch(f)
  {
    case '218da3':
      jQuery("#wpfx_layout [value='2']").attr("selected", "selected");
      jQuery('.layout_builder').hide();
      break;

    case '9wfy4z':
      jQuery("#wpfx_layout [value='1']").attr("selected", "selected");
      jQuery('.layout_builder').hide();
      break;

    default:
      jQuery("#wpfx_layout [value='3']").attr("selected", "selected");
      jQuery('.layout_builder').show();
      break;
  }
}

function onLayoutChange()
{
  if(jQuery('#wpfx_layout :selected').val() > 2 )
  {
    jQuery('.layout_builder').hide();
    jQuery('#loader').show();

    jQuery.ajax({dataType: "json", url: ajaxurl, type: "POST",
      timeout: 1000000,
      data:
    {
      'wpfx_layout' : jQuery('#wpfx_layout :selected:last').val(),
      'action'      : 'wpfx_get_layout'
    }, success: function(data)
    {  
      if(jQuery('#wpfx_layout :selected').val() == "3") 
    {
      jQuery('#clnewmap, [type="reset"], #remvcl, .cltable, #dupcl').hide();
      jQuery('#clbody').html('<tr><td><input name="clname" type="radio" class="radioname" value="name_0"></td><td><input name="clfrom[]"></td><td id="maps">»»</td><td><input name="clto[]"></td><td id="delete">×</td></tr>');
      jQuery('#savecl').val('Create Field Map');

    }
      else
    {
      jQuery('#clnewmap, [type="reset"], #remvcl, #dupcl, .cltable').show();
      jQuery('#clbody').html('');
      jQuery('#savecl').val('Save Field Map');
      jQuery("#wpfx_layoutvis").change(function () {
        var el = jQuery('#wpfx_layoutvis_options');
        if ( jQuery(this).val() != '2' )
          el.hide();
        else
          el.slideDown(500);
      });


    }
      jQuery('#loader').hide();
      jQuery('.layout_builder').show();
      jQuery('#wpfx_clname').val(data.name);

      jQuery('#wpfx_password').val(data.passwd);
      //console.log(data);
      //



      jQuery("#wpfx_clfile [value = '" + data.file + "']").attr("selected", "selected");
      jQuery("#wpfx_layoutvis [value = '" + data.visible + "']").attr("selected", "selected");
      jQuery("#wpfx_lang [value = '" + data.lang + "']").attr("selected", "selected");
      jQuery("#wpfx_add_att [value = '" + data.add_att + "']").attr("selected", "selected");
      jQuery("#wpfx_clform [value = '" + data.form + "']").attr("selected", "selected");
      jQuery("select#wpfx_default_format [value = '" + data.default_format + "']").attr("selected", "selected");
      jQuery("input#wpfx_default_format").val(data.default_format);
      jQuery("input#wpfx_name_email").val(data.name_email);
      jQuery("input#wpfx_restrict_user").val(data.restrict_user);
      jQuery("input#wpfx_restrict_role").val(data.restrict_role);
      jQuery("input#wpfx_name_email").attr('placeholder', data.name);
      jQuery("#wpfx_layoutvis").trigger('change');
       jQuery("#wpfx_lang").trigger('change');

      jQuery('#wpfx_add_att').unbind().change( function () {
        var val = jQuery(this).val();
        var el = jQuery('#wpfx_frm_actions, #wpfx_frm_actions2');
        if ( val + '' == '1' )
          el.show();
        else
          el.hide();
      }).trigger('change');

      jQuery('#clbody').empty();

      try
      {
        if ( jQuery('#frm-bg').data('automap') && data.data && data.data[0] && ( data.data.length == 1 ) && (data.data[0][0]) && (data.data[0][1] == "1") && data.fields2 )
        {
          data.data = new Array();
          function _transformName(s)
          {
            s = s + '';
            s = s.toLowerCase();
            s = s.replace(/^\[\d+\] /, '');
            s = s.replace(/[^a-z0-9]+/g, '');
            return s;
          }
          jQuery.each(data.fields2, function ( __index, __item ) {
            var _new_arr = new Array();
            var map_to = "1";
            jQuery.each(data.fields1, function ( __index2, __item2 ) {
              if ( __item2[2] && ( _transformName(  __item2[2] ) == _transformName(__item) ) )
                map_to = __item2[0];
              else
              {
                if ( _transformName(  __item2[1] ) == _transformName(__item) )
                  map_to = __item2[0];
              }
            });
            _new_arr.push( map_to );
            _new_arr.push( __item );
            data.data.push(_new_arr);
          });
        }
      }
      catch (e)
      {
        
      }

      jQuery.each(data.data, function(index, _item)
          {
            var i = _item[ 0 ];
            var item = _item[ 1 ];
            //var row = jQuery("<tr><td><input name = 'clname' type = 'radio' class = 'radioname' /></td><td><input name = 'clfrom[]' value = '" + i + "' /></td><td id = 'maps'>&raquo;&raquo;</td><td><input name = 'clto[]' value = '" + item + "' /></td><td id = 'delete' title='Delete this row'>&times;</td>").on('click', '#delete', function() {  jQuery(this).closest ('tr').remove (); } );
            //

            var row;
            var select2 = "<input name = 'clto[]' value = '" + item + "' />";
            if ( data.fields2 )
      {

        options = "";



        jQuery.each(data.fields2, function (j, field) {
          selected = "";
          if ( item+'' == field+'' )
          selected = ' selected="selected"';
          var _field = field;
          try
          {
            _field = _field.replace(/\#[A-Z0-9]{2}\#[A-Z0-9]{2}/i, function (m) { return decodeURIComponent( m.replace(/\#/g, '%') ); });
          }
          catch (e)
          {
            
          }
          options += '<option value="'+field+'"'+selected+' data-src="'+data.images[ field ]+'">'+_field+'</option>';
        });



        if ( data.previews_activated )
           select2 = "<select class='with-preview' name='clto[]' data-base='"+data.imagesBase+"'>"+options+"</select>";
        else
           select2 = "<select name='clto[]' data-base='"+data.imagesBase+"'>"+options+"</select>";

      }

      var select1 = "<input name = 'clfrom[]' value = '" + i + "' />";
      if ( data.fields1 )
      {

        options = "";
        jQuery.each(data.fields1, function (index, _field) {
          var key = _field[0];
          var field = _field[1];
          selected = "";
          if ( i+'' == key+'' )
          selected = ' selected="selected"';
        options += '<option value="'+key+'"'+selected+'>'+field+'</option>';
        });

        var fields3 = {}
        fields3.FPROPDF_ITEM_KEY = 'Formidable: Entry Key';
        fields3.FPROPDF_IP = 'Formidable: IP';
        fields3.FPROPDF_BROWSER = 'Formidable: Browser';
        fields3.FPROPDF_UPDATED_AT = 'Formidable: Updated At';
        fields3.FPROPDF_CREATED_AT = 'Formidable: Created At';
        fields3.FPROPDF_REFERRER = 'Formidable: Referrer';
        fields3.FPROPDF_USER_ID = 'Formidable: User ID';
        fields3.FPROPDF_DATASET_ID = 'Formidable: Dataset ID';
        fields3.FPROPDF_COUNTER1 = 'Formidable: Count Entry';
        fields3.FPROPDF_COUNTER2 = 'Formidable: Count PDF';

        for ( var field in fields3 )
        {
          selected = "";
          if ( i+'' == field+'' )
          selected = ' selected="selected"';
          options += '<option value="'+field+'"'+selected+'>'+fields3[ field ]+'</option>';
        }

        select1 = "<select name='clfrom[]'>"+options+"</select>";

      }

      var acts = jQuery('#wpfx_frm_actions');
      acts.html('');
      var add_att_ids = data.add_att_ids;
      if ( add_att_ids )
        add_att_ids = add_att_ids.split(',');
      else
        add_att_ids = new Array();
      if ( data.actions )
        for ( var act_id in data.actions )
        {
          var act_name = data.actions[ act_id ];
          var checked = '';
          jQuery.each( add_att_ids, function (__index, __val) {
            if ( __val + '' == act_id + '' )
              checked = ' checked="checked"';
            if ( __val + '' == 'all' )
              checked = ' checked="checked"';
          }) ;
          acts.append('<label><input type="checkbox" name="wpfx_add_att_ids[]" value="'+act_id+'" '+checked+'/> ' + act_name + '</label><br />');
        }

      if ( !data.formats[ index ] )
        data.formats[ index ] = new Array();

      var formats2 = {
        //none: '(no formatting)',
        tel: 'Telephone',
        address: 'Address',
        credit_card: 'Credit Card',
        date: 'MM/DD/YY',
        date2: 'DD/MM/YYYY',
        date3: 'MM/DD/YYYY',
        date4: 'YYYY/MM/DD',
        date5: 'DD-MM-YYYY',
        date6: 'DD.MM.YYYY',
        date7: 'DD. month year',
        curDate: 'MM/DD/YY',
        curDate2: 'DD/MM/YYYY',
        curDate3: 'MM/DD/YYYY',
        curDate4: 'YYYY/MM/DD',
        curDate5: 'DD-MM-YYYY',
        curDate6: 'DD.MM.YYYY',
        curDate7: 'DD. month YYYY',
        capitalize: 'Capitalize',
        capitalizeAll: 'CAPITALIZE ALL',
        returnToComma: 'Carriage return to comma',
		returnToCarriage: 'Comma to carriage return',
        signature: 'Signature',
        repeatable: 'Repeatable',
        repeatable2: 'Repeatable (separate fields)',
        label: 'Show label for checkbox/select/radio',
        removeEmptyLines: 'Remove empty lines in text',
        html: 'Remove HTML tags',
        number_f1: '1,000.00',
        number_f2: '1.000,00',
        number_f9: '1. 000, 00',
        number_f10: '1, 000. 00',
        number_f8: '1000,00',
        number_f3: '1 000.00',
        number_f4: '1 000,00',
        number_f5: '1,000',
        number_f6: '1.000',
        number_f7: '1 000'
        //checkbox: 'Multiple Checkboxes'
      };
      
      if ( window.fpropdfAdditionalFormatting )
      {
        var _index;
        for ( _index = 0; _index < window.fpropdfAdditionalFormatting.length; _index++ )
          formats2[ window.fpropdfAdditionalFormatting[ _index ] ] = window.fpropdfAdditionalFormatting[ _index ];
      }
      
      var hiddenFieldsKeysIndex;
      var hiddenFields = "";
      var hiddenFieldsKey = "";
      var hiddenFieldsKeys = new Array();
      hiddenFieldsKeys.push( 'number_f' );
      hiddenFieldsKeys.push( 'curDate' );
      hiddenFieldsKeys.push( 'date' );
      
      var options2 = "";
      
      if ( ! data.formats[index] )
        data.formats[index] = new Array();
      if ( ! data.formats[index].length < 2 )
      {
        data.formats[index].push("");
        data.formats[index].push("");
      }

      options2 += '<option value="none">(no formatting)</option>';
      options2 += '<option value="curDate"' + ( data.formats[index][1].indexOf('curDate') === 0 ? ' selected="selected"' :'' ) + '>Current Date</option>';
      options2 += '<option value="date"' + ( data.formats[index][1].indexOf('date') === 0 ? ' selected="selected"' :'' ) + '>Date</option>';
      options2 += '<option value="number_f"' + ( data.formats[index][1].indexOf('number_f') === 0 ? ' selected="selected"' :'' ) + '>Number</option>';

      var rep = addr = cc = "";
      for ( var key2 in formats2 )
      {
        hiddenFieldsKey = true;
        for ( hiddenFieldsKeysIndex = 0; hiddenFieldsKeysIndex < hiddenFieldsKeys.length; hiddenFieldsKeysIndex++ )
        {
          if ( key2.indexOf( hiddenFieldsKeys[hiddenFieldsKeysIndex] ) === 0 )
          {
            hiddenFieldsKey = false;
          }
        }

        if ( ! hiddenFieldsKey )
          continue;
        var selected2 = "";
        if ( data.formats[index][1] == key2+'' )
        {
          selected2 = ' selected="selected"';
        }
        else
        {
        }
        options2 += '<option value="'+key2+'"'+selected2+'>'+formats2[key2]+'</option>';
        if ( (data.formats[index][1] == 'repeatable') || (data.formats[index][1] == 'repeatable2') )
        {
          rep = data.formats[index][2];
          if ( !rep ) rep = "";
        }
        else if (data.formats[index][1] == 'address')
        {
            addr = data.formats[index][5];
            if ( !addr ) addr = "";
        }
        else if (data.formats[index][1] == 'credit_card')
        {
            cc = data.formats[index][6];
            if ( !cc ) cc = "";
        }
      }

      var list_id = 'list_' + ( new Date() ).getTime() + Math.round( Math.random() * 10000 );


      for ( hiddenFieldsKeysIndex = 0; hiddenFieldsKeysIndex < hiddenFieldsKeys.length; hiddenFieldsKeysIndex++ )
      {
        hiddenFieldsKey = hiddenFieldsKeys[ hiddenFieldsKeysIndex ];
        hiddenFields += "<div class='fpropdf_hidden_fields_opts fpropdf_hidden_fields_opts_" + hiddenFieldsKey + "' style='display: none; text-align: left;'>Format as:<br /><select name='select_for_" + hiddenFieldsKey + "[]'>";
        for ( var key2 in formats2 )
          if ( key2.indexOf(hiddenFieldsKey) === 0 )
          {
            selected2 = "";
            if ( data.formats[index][1] == key2+'' )
            {
              selected2 = ' selected="selected"';
            }
            hiddenFields += "<option value='" + key2 + "'" + selected2 + ">" + formats2[key2] + "</option>";
          }
        hiddenFields += "</select> </div>";
      }
      

      format2 = "<td>" +
      		"<select name='format[]' class='fpropdf_formatting_opt'>"+options2+"</select>" +
			"<textarea name='repeatable_field[]' style='display: none;' placeholder='Repeatable formatting...'></textarea>" +
			"<textarea name='address_field[]' style='display: none;' placeholder='Address formatting...'></textarea>" +
			"<textarea name='credit_card_field[]' style='display: none;' placeholder='Credit Card formatting...'></textarea>" +
			"<div class='checkbox_opts' style='text-align: left;'>Please enter the export value of this checkbox:<br />" +
			"<input name='checkbox_field[]' value='' list='"+list_id+"' /><datalist id='"+list_id+"'></datalist></div>" +
			"<div class='image_opts' style='display: none; text-align: left;'>Image Alignment:<br />" +
			"<select name='image_field[]'><option value='top_left'>Top Left</option><option value='top_center'>Top Center</option><option value='top_right'>Top Right</option><option value='bottom_left'>Bottom Left</option><option value='bottom_center'>Bottom Center</option><option value='bottom_right'>Bottom Right</option><option value='center_center'>Center Center</option></select>" +
			"</div> " + hiddenFields   + " </td>";


      if ( ! parseInt( jQuery('#clbody').data('activated') ) )
      {
        format2 = "";
      }

      row = jQuery("<tr><td><input name = 'clname' type = 'radio' class = 'radioname' /></td><td>"+select1+"</td><td id = 'maps'>&raquo;&raquo;</td><td>"+select2+"</td>"+format2+"<td id = 'delete' title='Delete this row'>&times;</td>").on('click', '#delete', function() {  jQuery(this).closest ('tr').remove (); } );

      row.find('.fpropdf_formatting_opt').change(function () {
        var _thisVal = jQuery(this).val();
        row.find('.fpropdf_hidden_fields_opts').hide();
        for ( hiddenFieldsKeysIndex = 0; hiddenFieldsKeysIndex < hiddenFieldsKeys.length; hiddenFieldsKeysIndex++ )
        {
          hiddenFieldsKey = hiddenFieldsKeys[ hiddenFieldsKeysIndex ];
          if ( hiddenFieldsKey == _thisVal )
          {
           row.find('.fpropdf_hidden_fields_opts_' + _thisVal).show();
          }
        }
        return true;
      });

      if ( ! parseInt( jQuery('#clbody').data('activated') ) )
      {
        row.find('td:last').append("<div class='checkbox_opts' style='text-align: left; display: none;'>Please enter the export value of this checkbox:<br /><input name='checkbox_field[]' value='' list='"+list_id+"' /><datalist id='"+list_id+"'></datalist></div>");
      }

      var reptxtarea = row.find('textarea[name^=repeatable_field]');
      reptxtarea.val( rep );
      var addrtxtarea = row.find('textarea[name^=address_field]');
      addrtxtarea.val( addr );
      var cctxtarea = row.find('textarea[name^=credit_card_field]');
      cctxtarea.val( cc );
      
      row.find('[name="format[]"]').change(function () {
        if ( (jQuery(this).find(':selected').val() == 'repeatable') || (jQuery(this).find(':selected').val() == 'repeatable2') )
        {
          jQuery(this).siblings('textarea').hide();
          jQuery(this).siblings('textarea[name^=repeatable_field]').show();
        }
        else if (jQuery(this).find(':selected').val() == 'address')
        {
    	  jQuery(this).siblings('textarea').hide(); 
          jQuery(this).siblings('textarea[name^=address_field]').show().val(addr ? addr : "FOR U.S. and OTHER ADDRESS\n\
[line1]\n\
[line2]\n\
[city] [state] [zip]\n\
\n\
FOR INTERN.\n\
[line1]\n\
[line2]\n\
[city] [state] [zip]\n\
[country]");
        }
        else if (jQuery(this).find(':selected').val() == 'credit_card')
        {
    	  jQuery(this).siblings('textarea').hide(); 
          jQuery(this).siblings('textarea[name^=credit_card_field]').show().val(cc ? cc : "[cc]\n[month]/[year]");
        }
        else
        {
      	  jQuery(this).siblings('textarea').hide();
        }
      }).trigger('change');

      row.find('[name="clto[]"]').change(function () {
        row.find('[name="format[]"]').trigger('change');
      });
      
      if ( data.formats && data.formats[index] && data.formats[index][4] )
        row.find('.image_opts option[value="' + data.formats[index][4] + '"]').prop('selected', true);
      
      row.find('[name="clfrom[]"]').change(function () {
        var _this_val = jQuery(this).find('option:selected').attr('value') + '';
        var image_fields = jQuery(this).parents('tr:first').find('.image_opts');
        image_fields.hide();
        if ( data.imageFields && data.imageFields.length && ( jQuery.inArray( _this_val, data.imageFields ) !== -1 ) )
          image_fields.show();
      }).trigger('change');

      row.find('.checkbox_opts input').val( data.formats[ index ][ 3 ] );
      
      var fopt = row.find('[name="format[]"]');
      if ( ! parseInt( jQuery('#clbody').data('activated') ) )
        fopt = row.find('[name="clto[]"]');
      
      fopt.change(function () {
        var _f = jQuery(this).parent().parent().find('.checkbox_opts');
        //var the_id = jQuery(this).parent().parent().find('[name="clfrom[]"]').find(':selected').val();
        var the_id = jQuery(this).parent().parent().find('[name="clto[]"]').find(':selected').val();
        row.find('.checkbox_opts input').val( data.checkboxes[ the_id ] );
        //if ( jQuery(this).find(':selected').val() == 'checkbox' )
        //{
          //if ( data.checkboxes[ the_id ] )
            //jQuery.each( data.checkboxes[ the_id ], function ( index9, val ) {
              //var _o = jQuery('<option />');
              //_o.attr('value', val);
              //jQuery('#' + list_id).append(_o);
            //});

          //_f.show();
        //}
        //else
          //_f.hide();
        _f.hide();
      }).trigger('change');


      jQuery('#clbody').append(row);

      row.find('select').trigger('change');
          });

      recalcRadio(data.index);
    }
    });


  }
  else
  {
    // if not custom layout, show form
    jQuery('.layout_builder').hide();

  }
}

function adjustDataset()
{

  jQuery('#wpfx_dataset').html('<option>Loading datasets...</option>').prop('disabled', true);

  jQuery.ajax({dataType: "json", url: ajaxurl, type: "POST",
    data:
  {
    'wpfx_form_key' : jQuery('#wpfx_form :selected').val(),
    'action'        : 'wpfx_get_dataset'
  }, success: function(data)
  {
    jQuery('#wpfx_dataset').find('option').remove();
    jQuery('#wpfx_dataset').prop('disabled', false);

    jQuery.each(data, function(i,item)
      {
        jQuery('#wpfx_dataset').append("<option value = '" + item.id + "'>" + item.date + "</option>");
        if ( item.date == "You MUST enter form data before creating merge!" )
      jQuery('#wpfx_dataset').prop('disabled', true);
      });
  }
  });
}

function adjustDataset2()
{

  jQuery('#wpfx_dataset2').html('<option>Loading datasets...</option>').prop('disabled', true);

  jQuery.ajax({dataType: "json", url: ajaxurl, type: "POST",
    data:
  {
    'wpfx_form_key' : jQuery('#wpfx_form2 :selected').val(),
    'action'        : 'wpfx_get_dataset'
  }, success: function(data)
  {
    jQuery('#wpfx_dataset2').find('option').remove();
    jQuery('#wpfx_dataset2').prop('disabled', false);

    jQuery.each(data, function(i,item)
      {
        jQuery('#wpfx_dataset2').append("<option value = '" + item.id + "'>" + item.date + "</option>");
        if ( item.date == "You MUST enter form data before creating merge!" )
      jQuery('#wpfx_dataset2').prop('disabled', true);
      });
  }
  });
}
// function adjustLayoutVisibility()
// {
//     var f = jQuery('#wpfx_layout :selected').val();
//     jQuery('#loader').show();
//
//     jQuery.ajax({dataType: "json", url: ajaxurl, type: "POST",
//                 data: { 'wpfx_layout' : f, 'action': 'wpfx_getlayoutvis' },
//                 success: function(data)
//                 {
//                     jQuery('#loader').hide();
//
//                     jQuery("#wpfx_layoutvis [value = '" + data.visible + "']").attr("selected", "selected");
//                     jQuery("#wpfx_clform [value = '" + data.form + "']").attr("selected", "selected");
//                 } });
// }

jQuery(document).ready(function()
    {

      if ( !window.ajaxurl )
  return;

jQuery('#wpfx_preview').click(function()
  {
    jQuery(this).attr('href', 'admin-ajax.php?action=frm_forms_preview&form=' + jQuery('#wpfx_form :selected').val());
  });

jQuery('#wpfx_preview2').click(function()
  {
    jQuery(this).attr('href', 'admin-ajax.php?action=frm_forms_preview&form=' + jQuery('#wpfx_form2 :selected').val());
  });

jQuery('#wpfx_layout').on('change', function()
  {
    onLayoutChange();
    //         adjustLayoutVisibility();
  });


jQuery('#clnewmap').click(function() {
	if ( jQuery('#clbody tr').length ) {
		var row = jQuery('#clbody tr:last').clone(true);
		row.find('[name="clfrom[]"]').trigger('change');
	} else {
		var row = jQuery("<tr><td><input checked='checked' name = 'clname' type = 'radio' class = 'radioname' /></td><td><input  value='1' name = 'clfrom[]' /></td><td id = 'maps'>&raquo;&raquo;</td><td><input  value='1' name = 'clto[]' /></td><td id = 'delete'>&times;</td>").on('click', '#delete', function() {
			jQuery(this).closest('tr').remove ();
			recalcRadio();
		});
	}
	jQuery('#clbody').append(row);
	recalcRadio();
});

jQuery('#wpfx_form').on('change', function()
    {
      adjustLayout();
      adjustDataset();
      //adjustFormVisibility();
    });

jQuery('#wpfx_form2').on('change', function()
    {
      adjustDataset2();
      //adjustFormVisibility();
    });
//     jQuery('#wpfx_layoutvis').on('change', function()
//     {
//         jQuery('#loader').show();
//
//         jQuery.ajax({dataType: "json", url: ajaxurl, type: "POST",
//                     data: {
//                         'wpfx_layout':            jQuery('#wpfx_layout :selected').val(),
//                         'wpfx_layout_visibility': jQuery('#wpfx_layoutvis :selected').val(),
//                         'action': 'wpfx_setlayoutvis'
//                     },
//                     success: function(data)
//                     {
//                         jQuery('#loader').hide();
//                     } });
//     });

jQuery('#hideme').click(function()
    {
      jQuery('#dform').submit();

      return true;
    });

jQuery('#remvcl').click(function()
    {
      var layout_name = jQuery('#wpfx_clname').val();
      var btn = jQuery(this);
      if ( !confirm('Are you sure you want to delete layout "'+layout_name+'"?') )
  return false;
if ( !confirm('This action cannot be undone. Do you really want to delete layout "'+layout_name+'"?') )
  return false;
if(true)
{
  if(jQuery('#wpfx_layout :selected').val() == 3)
{
  alert('Cant delete this');
} else 
{
  btn.prop('disabled', true);
  btn.val('Please wait...');
  jQuery.ajax({dataType: "json", url: ajaxurl, type: "POST",
    data:
  {
    'wpfx_layout' : jQuery('#wpfx_layout :selected').val(),
    'action'      : 'wpfx_del_layout'
  }, success: function(data)
  {
    location.reload();
  } });
}
}
});

jQuery('#dupcl').click(function()
    {
      var layout_name = jQuery('#wpfx_clname').val();
      var btn = jQuery(this);
      btn.prop('disabled', true);
      btn.val('Please wait...');
      jQuery.ajax({url: ajaxurl, type: "POST",
        data:
      {
        'wpfx_layout' : jQuery('#wpfx_layout :selected').val(),
        'action'      : 'wpfx_dup_layout'
      }, success: function(data)
      {
        location.reload();
      } });
    });

jQuery('#savecl').click(function()
    {

        var _name = jQuery('#wpfx_clname').val();
        var found = false;
        jQuery('#wpfx_layout option').each(function () {
          if ( jQuery(this).text() == _name )
            if ( jQuery(this).val() + '' != jQuery('#wpfx_layout :selected').val() )
              found = true;
        });
        if ( found )
        {
          alert('Please choose another field map name. "'+_name+'" is already used.');
          jQuery('#wpfx_clname').focus();
          return false;
        }

      if(jQuery('#wpfx_layout :selected').val() == "3") 
{



}
if( ! jQuery('#clbody tr').length)
{
  var row = jQuery("<tr><td><input checked='checked' name = 'clname' type = 'radio' class = 'radioname' /></td><td><input value='1' name = 'clfrom[]' /></td><td id = 'maps'>&raquo;&raquo;</td><td><input  value='1' name = 'clto[]' /></td><td id = 'delete'>&times;</td>").on('click', '#delete', function() {  jQuery(this).closest ('tr').remove (); recalcRadio(); } );
  jQuery('#clbody').append(row);
} else if(! jQuery("input:radio:checked").length )
{
  alert('Please pick a name for dataset first');
  return false;
}
else
{



  if(jQuery('#wpfx_layout :selected').val() > 3) // we're updating existing layout
  {


    jQuery('#wpfx_layout_form [name="update"]').remove();
    jQuery('<input>').attr({
      type: 'hidden',
      id: 'update',
      name: 'update'}).val('update').appendTo('#wpfx_layout_form');

    jQuery('#wpfx_layout_form [name="wpfx_layout_visibility"]').remove();
    jQuery('<input>').attr({
      type: 'hidden',
      id: 'wpfx_layout_visibility',
      name: 'wpfx_layout_visibility'}).val(jQuery('#wpfx_layoutvis :selected').val()).appendTo('#wpfx_layout_form');


    jQuery('#wpfx_layout_form [name="wpfx_layout"]').remove();
    jQuery('<input>').attr({
      type: 'hidden',
      id: 'wpfx_layout',
      name: 'wpfx_layout'}).val(jQuery('#wpfx_layout :selected').val()).appendTo('#wpfx_layout_form');
  
    if ( typeof(Blob) != "undefined" )
    {
  
      var sData = JSON.stringify( jQuery('#wpfx_layout_form').serializeObject() );
  
      var formData = new FormData( jQuery('#wpfx_layout_form')[0] );
      formData.append("wpfx_savecl", "1");
      
      var blob = new Blob([sData], { type: "text/plain"} );
      
      formData.append("postdata", blob);
    
      
      var xhr = new XMLHttpRequest();
      xhr.open("POST", window.location.href, true);
      // xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      //xhr.setRequestHeader("Content-Length", body.length);
      //xhr.setRequestHeader("Connection", "close");
      xhr.onreadystatechange = function () {
         if ( xhr.readyState == 4 )
         {
           if (xhr.status == 200) {
             jQuery('#wpfx_layout_form #savecl').prop('disabled', true).val('Wait...');
             // console.log(xhr.responseText); return;
             window.location.href = 'admin.php?page=fpdf&wpfx_form=' + encodeURIComponent( jQuery('#wpfx_form').val() ) + '&wpfx_layout=' + encodeURIComponent( jQuery('#wpfx_layout').val() ) + '&wpfx_dataset=' + encodeURIComponent( jQuery('#wpfx_dataset').val() );
           } else {
             alert('Server returned an error while saving field map.');
             jQuery('#wpfx_layout_form #savecl').prop('disabled', true).val('Save');
           }
         }
      }
      xhr.timeout = 300000; // Set timeout to 4 seconds (4000 milliseconds)
      xhr.ontimeout = function () { 
        alert("Connection to the server timed out."); 
        jQuery('#wpfx_layout_form #savecl').prop('disabled', true).val('Save');
      }
      xhr.send(formData);
      jQuery('#wpfx_layout_form #savecl').prop('disabled', true).val('Saving...');
      return false;
    
    }


  }

return true;
}
});

jQuery.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    jQuery.each(a, function() {
        if ( this.name.indexOf('[]') > 0 )
        {
          this.name = this.name.replace('[]', '');
          if ( o[this.name] === undefined )
            o[this.name] = new Array();
        }
        if ( o[this.name] !== undefined ) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

adjustLayout();
onLayoutChange();

if ( window.currentSelectedLayout )
{
  jQuery('#wpfx_layout option').removeAttr('selected');
  jQuery('#wpfx_layout [value="' + window.currentSelectedLayout + '"]').prop('selected', true);
  jQuery('#wpfx_layout').trigger('change');
}

adjustDataset();

adjustDataset2();
//     adjustLayoutVisibility();
});
