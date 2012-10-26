jQuery ($) ->

	updateValue = ($control) ->
		added = ($(i).attr('data-id') for i in $('li.added', $control))

		$('input', $control).val JSON.stringify added


	$('.tree-select li > .item > .add').on 'click', ->
		$item = $(@).closest 'li'
		$control = $(@).closest '.tree-select'

		return false if $item.hasClass 'added'

		$item.addClass 'added'

		$added = $ '<li />'
		$added.html $item.attr 'data-aloneName'
		$added.prepend $ '<span class="remove">odebrat</span>'
		$added.data 'treeItem', $item

		$('ul.added', $control).append $added

		updateValue($control)

		false


	$('.tree-select li > .item').on 'click', ->
		$(@).closest('li').toggleClass 'opened'


	$(document).on 'click', '.tree-select .remove', ->
		$item = $(@).closest 'li'
		$control = $(@).closest '.tree-select'

		$item.data('treeItem').removeClass 'added'

		$item.remove()

		updateValue($control)

		false


	# init
	$('.tree-select li.added').each ->
		$(@).removeClass 'added'
		$('> .item > .add', @).trigger 'click'
