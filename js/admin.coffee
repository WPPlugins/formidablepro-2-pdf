jQuery(document).ready ($) ->

  $('#wpfx_form, #wpfx_form2').on 'change upddisabled', ->
    return true unless $('#frm-bg').data('limitdropdowns')
    allowed = $(this).find(':selected').data('allowedlayouts')
    allowed ||= []
    allowed.push(3 - 9)
    # console.log 'changed', $(this).attr('id'), allowed
    el = $ '#wpfx_layout'
    el = $ '#wpfx_layout2' if $(this).attr('id') == 'wpfx_form2'
    el.find('option').each ->
      opt = $ this
      for id in allowed
        a = parseInt(id)+9
        b = parseInt(opt.attr('value'))
        # console.log a, b, opt.text()
        if a == b
          opt.prop 'disabled', false
          return true
      opt.prop 'disabled', true
      true
    true
    
  $('#wpfx_form2').on 'change', ->
    $(this).find('option').prop 'selected', false
    true
    
  $('#wpfx_form, #wpfx_form2').trigger 'upddisabled'

  $('.embedded_options_field_template').each ->
    $(this).find('.button_del').click ->
      $(this).parent().remove() if confirm 'Do you really what to delete this embedded form field map?'
      false
    $(this).find('select:first').change ->
      sel2 = $(this).parent().find('select:last')
      currentFormId = $(this).val()
      sel2.find('option').not( sel2.find('option:first') ).remove()
      fields = window.fpropdfEmbeddedFieldData.fields[ currentFormId + ''  ]
      # console.log fields, currentFormId
      if fields
        for field in fields
          opt = $ '<option />'
          opt.attr 'value', field.id
          opt.html field.title
          sel2.append opt
    true

  embeddedMap = (embeddedData) ->
    tpl = $('.embedded_options_field_template').clone true
    tpl.appendTo '.embedded_options_field_container'
    tpl.find('[data-name]').each ->
      name = $(this).attr 'data-name'
      name = name.replace 'NUMBER', ( tpl.index() - 1 )
      $(this).attr 'name', name
    tpl.removeClass 'embedded_options_field_template'
    if embeddedData
      currentFormId = embeddedData.form
      if currentFormId
        tpl.find('select:first option').removeAttr 'selected'
        tpl.find('select:first option[value="' + currentFormId + '"]').attr('selected', 'selected')
        tpl.find('select:first').trigger 'change'
      currentFieldId = embeddedData.field
      if currentFormId
        tpl.find('select:last option').removeAttr 'selected'
        tpl.find('select:last option[value="' + currentFieldId + '"]').attr('selected', 'selected')
        tpl.find('select:last').trigger 'change'

  $('#fpropdf_add_embedded_form').click ->
    embeddedMap()
    false
    
  if window.fpropdfEmbeddedFieldData
    currentData = window.fpropdfEmbeddedFieldData.currentData
    if currentData
      if currentData.length
        for embeddedData in currentData
          if embeddedData.field and embeddedData.form
            if embeddedData.field.length and embeddedData.form.length
              embeddedMap embeddedData
    
  $('#fpropdf_add_embedded_form').click()

  $('#use-second-layout').change ->
    $('.hidden-use-second').toggle()
    true

  container = $ '#wpbody-content .parent ._first'

  div = $ '<div />'
  div
    .addClass 'formidable-shortcode-container'
    .html 'Shortcode for Download Link or Button&nbsp;'
    .insertAfter container
    .hide()

  span = $ '<input />'
  span
    .appendTo div

  activated = parseInt $('#frm-bg').data('activated')
  security = parseInt $('#frm-bg').data('security')
  pdfaid = parseInt $('#frm-bg').data('pdfaid')
  # pdfaid = false

  if activated
    div.append 'Shortcode for Automatic Download'

  span2 = $ '<input />'

  if activated
    span2
      .appendTo div

  if activated and pdfaid
    div.append 'Shortcode for Downloading DOCX'

  span4 = $ '<input />'

  if activated and pdfaid
    span4
      .appendTo div


  span3 = $ '<span />'
  if activated
    span3
      .appendTo div
      
  span5 = $ '<span />'
  if activated and pdfaid
    span5
      .appendTo div

  div.find('input')
    #.prop "readonly", true
    .focus ->
      this.select()
      true 

  main_form = container.find "form"
  oldData = false
  setInterval ->
    data = main_form.serialize()
    if oldData != data
      setShortcode()
    oldData = data
  , 10

  setShortcode = ->
    form = $('#wpfx_form').val()
    form2 = $('#wpfx_form2').val()
    dataset = $('#wpfx_dataset').val()
    dataset2 = $('#wpfx_dataset2').val()
    layout = $('#wpfx_layout').val()
    layout2 = $('#wpfx_layout2').val()
    dataset ||= '-3'
    dataset2 ||= '-3'
    dataset = '-3' if dataset == "Loading datasets..."
    dataset2 = '-3' if dataset2 == "Loading datasets..."
    datasetOrig = ""
    datasetOrig2 = ""
    if layout != '3' and form
      div.slideDown()
      $('#tr-export').slideDown()
    else
      div.slideUp()
      $('#tr-export').slideUp()
    if dataset != '-3'
      datasetOrig = dataset
      dataset = " dataset=\"#{dataset}\""
    else
      dataset = ""
    if dataset2 != '-3'
      datasetOrig2 = dataset2
      dataset2 = " dataset2=\"#{dataset2}\""
    else
      dataset2 = ""

    second = ""
    secondUrl = ""
    if $('#use-second-layout').is ':checked'
      second = " form2=\"#{form2}\"#{dataset2} layout2=\"#{layout2}\""
      secondUrl = "&form2=#{form2}&dataset2=#{datasetOrig2}&layout2=#{layout2}"

    span.val "[formidable-download form=\"#{form}\"#{dataset} layout=\"#{layout}\"#{second}]"
    span4.val "[formidable-download form=\"#{form}\"#{dataset} layout=\"#{layout}\"#{second} format=\"docx\"]"
    span2.val "[formidable-download form=\"#{form}\"#{dataset} layout=\"#{layout}\"#{second} download=\"auto\"]"
    span3.html ''
    span5.html ''

    securityPart = ''
    securityPart = '&redirect_to_secure=1' if security

    if dataset.length
      span3.html "<a href='#{window.ajaxurl}?action=wpfx_generate&form=#{form}&layout=#{layout}&dataset=#{datasetOrig}#{secondUrl}&format=pdf&inline=1#{securityPart}' target='_blank' class='button'>Preview (PDF)</a>"
      if pdfaid
        span5.html "&nbsp; <a href='#{window.ajaxurl}?action=wpfx_generate&form=#{form}&layout=#{layout}&dataset=#{datasetOrig}#{secondUrl}&format=docx&inline=1#{securityPart}' target='_blank' class='button'>Preview (DOCX)</a>"
    $('#main-export-btn').attr 'href', "#{window.ajaxurl}?action=wpfx_generate&form=#{form}&layout=#{layout}&dataset=#{datasetOrig}#{secondUrl}&format=pdf&inline=1#{securityPart}"
    $('#main-export-btn-docx').attr 'href', "#{window.ajaxurl}?action=wpfx_generate&form=#{form}&layout=#{layout}&dataset=#{datasetOrig}#{secondUrl}&format=docx&inline=1#{securityPart}"

  container.find('select, input[type="checkbox"]').change setShortcode

  if false
    setInterval ->
      bottom = $ '.layout_builder'
      if bottom.is ':visible'
        #div.css 'width', bottom.outerWidth()
        div.show()
        #setShortcode()
    , 10


  $(document).on 'change', '#wpfx_layout_form table table select.with-preview', ->
    select = $ this
    select.parent().find('.custom-preview-img').remove()
    img = $ '<img />'
    img.addClass 'custom-preview-img'
    img.insertAfter select
    base = select.data "base"
    src = select.find('option:selected').data "src"
    #return unless src
    #return if src == "undefined"
    #return if src.test /^FPROPDF/
    #return unless select.val()
    #return if select.val() == "undefined"
    #return if select.val().test /^FPROPDF/
    src = "admin-ajax.php?page=fpdf&action=wpfx_preview_pdf&file=" + encodeURIComponent( $('#wpfx_clfile').val() ) + "&field=" + encodeURIComponent( select.val() ) + "&form=" + encodeURIComponent( $('#wpfx_clform').val() ) + "&ext=.png"
    #if base.length and src.length
      #img.attr 'src', "#{base}#{sa rc}" a  
    #else
      #img.remove()
    img.wrap "<a href='#{src}&TB_iframe=true' target='_blank' class='thickbox' />"
    img.load ->
      img.css 'max-width', 'initial'
      a = img.parent()
      href = a.attr 'href'
      href += "&width=" + ( img.width() - ( 626 - 597 ) ) + "&height=" + (img.height() - ( 200 - 188  ))
      a.attr 'href', href
      img.css 'max-width', ''
    img.attr 'src', src

  $(document).on 'click', '.upl-new-pdf', ->
    overlay = $ '<div />'
    overlay
      .addClass 'fpropdf-overlay'
      .appendTo 'body'
    form = $ '<form method="post" enctype="multipart/form-data" />'
    form
      .append 'Select a file: <input type="hidden" name="action" value="upload-pdf-file" /> <input accept="application/pdf" required="required" type="file" name="upload-pdf" />'
      .append '<input type="submit" class="button-primary" value="Upload" /> &nbsp; <a href="#" class="button cancel">Cancel</a>'
      .appendTo overlay
      .submit ->
        btn = $(this).find('.button-primary')
        setTimeout ->
          btn
            .html 'Please wait...'
            .val 'Please wait...'
            .prop 'disabled', true
        , 50
        true
      .wrapInner '<div />'
    form.find('.cancel').click ->
      overlay.remove()
      false
    false

  $(document).on 'click', 'input.remove-pdf', ->
    option = $('#wpfx_clfile option:selected')
    btn = $ this
    fnameEncoded = option.attr 'value'
    html = btn.val()
    fname = option.text()
    return false unless confirm("Are you sure you want to delete #{fname} ?")
    return false unless confirm("This action cannot be undone. The file #{fname} will be lost. Do you really want to delete it?")
    #console.log 'delete', fnameEncoded
    btn
      .prop 'disabled', true
      .html 'Please wait...'
    $.ajax
      url: window.ajaxurl
      data:
        file: fnameEncoded
        action: 'fpropdf_remove_pdf'
      method: 'POST'
      success: ->
        option.remove()
        alert "#{fname} has been removed."
        btn
          .prop 'disabled', false
          .val html
    false

  $(document).on 'click', '.fpropdf-activate', ->
    overlay = $ '<div />'
    overlay
      .addClass 'fpropdf-overlay'
      .appendTo 'body'
    form = $ '<form method="post" enctype="multipart/form-data" />'
    form
      .append 'Your activation code: <br /><br /> <input type="hidden" name="action" value="activate-fpropdf" /> <input type="text" name="activation-code" value="" required="required" placeholder="Paste your activation code here..." style="width: 300px; text-align: center;" /><br /><br />'
      .append '<input type="submit" class="button-primary" value="Activate" /> &nbsp; <a href="#" class="button cancel">Cancel</a>'
      .append '<br /><br /><a href="http://formidablepro2pdf.com/" target="_blank">How can I get an activation code?</a>'
      .appendTo overlay
      .submit ->
        btn = $(this).find('.button-primary')
        setTimeout ->
          btn
            .html 'Please wait...'
            .val 'Please wait...'
            .prop 'disabled', true
        , 50
        true
      .wrapInner '<div />'
    form.find('.cancel').click ->
      overlay.remove()
      false
    form.find('input[type="text"]').focus()

    false
