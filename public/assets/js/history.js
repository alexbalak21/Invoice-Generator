/**
 * history.js — delete-confirm modal wiring
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var confirmModal = document.getElementById('confirmDelete');
		if (!confirmModal) return;

		confirmModal.addEventListener('show.bs.modal', function (e) {
			var btn   = e.relatedTarget;
			var id    = btn.dataset.id;
			var type  = btn.dataset.type;
			var label = btn.dataset.label;

			var deleteLabel = document.getElementById('deleteLabel');
			var deleteForm  = document.getElementById('deleteForm');

			if (deleteLabel) deleteLabel.textContent = label;
			if (deleteForm) {
				deleteForm.action = 'history.php?action=delete&type=' + encodeURIComponent(type) + '&id=' + encodeURIComponent(id);
			}
		});
	});
}());
