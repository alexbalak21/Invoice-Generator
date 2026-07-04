/**
 * editable-table.js — contenteditable table navigation and synchronization
 */
(function () {
	'use strict';

	window.FormApp = window.FormApp || {};

	function getEditableCells(row) {
		return Array.from(row.querySelectorAll('[data-field]'));
	}

	function getRows() {
		return Array.from(document.querySelectorAll('[data-item-row]'));
	}

	function getCellCoordinates(cell) {
		var row = cell.closest('[data-item-row]');
		if (!row) return null;
		var rows = getRows();
		var rowIndex = rows.indexOf(row);
		var cells = getEditableCells(row);
		var colIndex = cells.indexOf(cell);
		return { row: row, rowIndex: rowIndex, colIndex: colIndex };
	}

	function getCell(rowIndex, colIndex) {
		var rows = getRows();
		if (rowIndex < 0 || rowIndex >= rows.length) return null;
		var cells = getEditableCells(rows[rowIndex]);
		return cells[colIndex] || null;
	}

	function focusAndSelect(cell) {
		if (!cell) return;
		cell.focus();
		var range = document.createRange();
		range.selectNodeContents(cell);
		var selection = window.getSelection();
		selection.removeAllRanges();
		selection.addRange(range);
	}

	function ensureNewRowIfNeeded(targetRowIndex) {
		var rows = getRows();
		while (targetRowIndex >= rows.length) {
			if (window.FormApp.addItemRow) {
				window.FormApp.addItemRow({}, rows.length);
				rows = getRows();
			} else {
				return;
			}
		}
	}

	function moveToCell(cell, rowOffset, colOffset) {
		var coord = getCellCoordinates(cell);
		if (!coord) return null;
		var target = getCell(coord.rowIndex + rowOffset, coord.colIndex + colOffset);
		if (target) {
			focusAndSelect(target);
		}
		return target;
	}

	function getNextCell(cell, backwards) {
		var coord = getCellCoordinates(cell);
		if (!coord) return null;
		var rows = getRows();
		var cells = getEditableCells(coord.row);
		var nextCol = coord.colIndex + (backwards ? -1 : 1);
		if (nextCol >= 0 && nextCol < cells.length) {
			return cells[nextCol];
		}
		var nextRowIndex = coord.rowIndex + (backwards ? -1 : 1);
		if (nextRowIndex < 0) return null;
		ensureNewRowIfNeeded(nextRowIndex);
		var nextRow = getCell(nextRowIndex, 0) ? getRows()[nextRowIndex] : null;
		if (!nextRow) return null;
		var nextRowCells = getEditableCells(nextRow);
		if (backwards) {
			return nextRowCells[nextRowCells.length - 1] || null;
		}
		return nextRowCells[coord.colIndex] || nextRowCells[0] || null;
	}

	function isSelectionAtStart(cell) {
		var selection = window.getSelection();
		if (!selection.rangeCount) return false;
		var range = selection.getRangeAt(0);
		return range.collapsed && cell.contains(range.startContainer) && range.startOffset === 0;
	}

	function isSelectionAtEnd(cell) {
		var selection = window.getSelection();
		if (!selection.rangeCount) return false;
		var range = selection.getRangeAt(0);
		return range.collapsed && cell.contains(range.endContainer) && range.endOffset === range.endContainer.textContent.length;
	}

	function syncRow(cellOrRow) {
		var row = cellOrRow.closest ? cellOrRow.closest('[data-item-row]') : null;
		if (!row) return;
		if (window.FormApp.syncRowHiddenInputs) {
			window.FormApp.syncRowHiddenInputs(row);
		}
	}

	function syncAllRows() {
		getRows().forEach(function (row) {
			if (window.FormApp.syncRowHiddenInputs) {
				window.FormApp.syncRowHiddenInputs(row);
			}
		});
	}

	function setCellValue(cell, value) {
		if (!cell) return;
		cell.textContent = value !== undefined && value !== null ? value : '';
		syncRow(cell);
	}

	document.addEventListener('DOMContentLoaded', function () {
		syncAllRows();

		// Ensure hidden inputs are synced before actual form submission
		var form = document.getElementById('documentForm');
		if (form) {
			form.addEventListener('submit', function () {
				syncAllRows();
			});
		}

		// Ensure hidden inputs and local draft are saved before page unload (refresh / navigation)
		window.addEventListener('beforeunload', function () {
			if (window.FormApp && window.FormApp.saveDraft) {
				try { window.FormApp.saveDraft(); } catch (e) {}
			}
			syncAllRows();
		});

		document.addEventListener('keydown', function (e) {
			var cell = e.target.closest('[data-field]');
			if (!cell) return;

			if (e.key === 'Tab') {
				e.preventDefault();
				var next = getNextCell(cell, e.shiftKey);
				if (next) focusAndSelect(next);
				return;
			}

			if (e.key === 'Enter') {
				e.preventDefault();
				var coord = getCellCoordinates(cell);
				if (!coord) return;
				var target = getCell(coord.rowIndex + 1, coord.colIndex);
				if (!target) {
					ensureNewRowIfNeeded(coord.rowIndex + 1);
					target = getCell(coord.rowIndex + 1, coord.colIndex);
				}
				if (target) focusAndSelect(target);
				return;
			}

			if (e.key === 'ArrowRight' && isSelectionAtEnd(cell)) {
				e.preventDefault();
				var next = getNextCell(cell, false);
				if (next) focusAndSelect(next);
				return;
			}

			if (e.key === 'ArrowLeft' && isSelectionAtStart(cell)) {
				e.preventDefault();
				var prev = getNextCell(cell, true);
				if (prev) focusAndSelect(prev);
				return;
			}

			if (e.key === 'ArrowDown') {
				e.preventDefault();
				var coord = getCellCoordinates(cell);
				if (!coord) return;
				var next = getCell(coord.rowIndex + 1, coord.colIndex);
				if (!next) {
					ensureNewRowIfNeeded(coord.rowIndex + 1);
					next = getCell(coord.rowIndex + 1, coord.colIndex);
				}
				if (next) focusAndSelect(next);
				return;
			}

			if (e.key === 'ArrowUp') {
				e.preventDefault();
				var coord = getCellCoordinates(cell);
				if (!coord) return;
				var prev = getCell(coord.rowIndex - 1, coord.colIndex);
				if (prev) focusAndSelect(prev);
				return;
			}
		});

		document.addEventListener('paste', function (e) {
			var cell = e.target.closest('[data-field]');
			if (!cell) return;
			e.preventDefault();

			var pasteText = (e.clipboardData || window.clipboardData).getData('text/plain');
			if (!pasteText) return;

			var rows = pasteText.replace(/\r/g, '').split('\n');
			var coord = getCellCoordinates(cell);
			if (!coord) return;

			rows.forEach(function (rowText, rowOffset) {
				var values = rowText.split('\t');
				var targetRowIndex = coord.rowIndex + rowOffset;
				ensureNewRowIfNeeded(targetRowIndex);
				values.forEach(function (value, valueOffset) {
					var targetCell = getCell(targetRowIndex, coord.colIndex + valueOffset);
					if (!targetCell) return;
					setCellValue(targetCell, value.trim());
				});
			});

			var lastRowIndex = coord.rowIndex + rows.length - 1;
			var lastColIndex = coord.colIndex + (rows[rows.length - 1].split('\t').length - 1);
			var lastCell = getCell(lastRowIndex, lastColIndex);
			if (lastCell) focusAndSelect(lastCell);
		});

		document.addEventListener('input', function (e) {
			var cell = e.target.closest('[data-field]');
			if (!cell) return;
			syncRow(cell);
			if (window.FormApp.updateFormTotals) {
				window.FormApp.updateFormTotals();
			}

			// Debounced save draft to localStorage
			if (window.FormApp && window.FormApp.saveDraft) {
				if (window._dg_save_timer) clearTimeout(window._dg_save_timer);
				window._dg_save_timer = setTimeout(function () {
					window.FormApp.saveDraft();
				}, 600);
			}
		});
	});

	window.FormApp.syncAllRows = syncAllRows;
}());
