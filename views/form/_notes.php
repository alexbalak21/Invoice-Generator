<div class="card shadow-sm border-0">
	<div class="card-body p-4">
		<h2 class="h5 mb-3">Notes &amp; Terms</h2>
		<div class="row g-3">

			<!-- Public notes -->
			<div class="col-12">
				<label class="form-label">Public notes</label>
				<textarea class="form-control" rows="3" name="notes[public]"><?= h($notes['public'] ?? '') ?></textarea>
			</div>

			<!-- Internal notes -->
			<div class="col-12">
				<label class="form-label">Internal notes <span class="text-muted small">(not printed)</span></label>
				<textarea class="form-control" rows="2" name="notes[internal]"><?= h($notes['internal'] ?? '') ?></textarea>
			</div>

			<!-- Bank account selector -->
			<div class="col-12">
				<label class="form-label fw-semibold">Bank account</label>
				<div class="d-flex gap-3 flex-wrap">
					<?php
					$bankOptions = [
						'international' => 'International (EUR/USD)',
						'french'        => 'French (domestic)',
						'none'          => 'None — do not print',
					];
					foreach ($bankOptions as $val => $label):
					?>
					<div class="form-check">
						<input class="form-check-input" type="radio"
							name="bank_account"
							id="bank_<?= $val ?>"
							value="<?= $val ?>"
							<?= ($bankAccount ?? 'none') === $val ? 'checked' : '' ?>>
						<label class="form-check-label" for="bank_<?= $val ?>">
							<?= h($label) ?>
						</label>
					</div>
					<?php endforeach; ?>
				</div>
				<div class="form-text">Controls which IBAN block appears on the printed document.</div>
			</div>

			<!-- Terms & Conditions dynamic list -->
			<div class="col-12">
				<label class="form-label fw-semibold">Terms &amp; Conditions</label>
				<div class="form-text mb-2">Each line prints as <b>Title</b>: Description. Leave title empty to print description only.</div>

				<div id="termsLinesList">
					<?php
					$tLines = $termsLines ?? [];
					if (empty($tLines)) {
						$tLines = [['title' => '', 'description' => '']];
					}
					foreach ($tLines as $i => $line):
					?>
					<div class="terms-line-row d-flex gap-2 mb-2 align-items-start" data-index="<?= $i ?>">
						<input type="text"
							class="form-control form-control-sm"
							style="width: 170px; flex-shrink: 0;"
							name="terms_lines[<?= $i ?>][title]"
							placeholder="Title (optional)"
							value="<?= h($line['title'] ?? '') ?>">
						<input type="text"
							class="form-control form-control-sm flex-grow-1"
							name="terms_lines[<?= $i ?>][description]"
							placeholder="Description"
							value="<?= h($line['description'] ?? '') ?>">
						<button type="button" class="btn btn-outline-danger btn-sm terms-line-remove" title="Remove line">
							&times;
						</button>
					</div>
					<?php endforeach; ?>
				</div>

				<button type="button" class="btn btn-outline-secondary btn-sm mt-1" id="termsLineAdd">
					+ Add line
				</button>
			</div>

			<!-- Acceptance (quotes) -->
			<?php if ($sections['acceptance'] ?? false): ?>
			<div class="col-12">
				<label class="form-check-label">
					<input class="form-check-input me-2" type="checkbox"
						name="acceptance[enabled]" value="1"
						<?= !empty($state['acceptance']['enabled'] ?? true) ? 'checked' : '' ?>>
					Include acceptance text in the printed document
				</label>
			</div>
			<div class="col-12">
				<label class="form-label">Acceptance text</label>
				<textarea class="form-control" rows="2" name="acceptance[text]"><?= h($state['acceptance']['text'] ?? 'Quote received before execution, read and approved, agreed.') ?></textarea>
			</div>
			<?php endif; ?>

		</div>
	</div>
</div>

<script>
(function () {
	const list   = document.getElementById('termsLinesList');
	const addBtn = document.getElementById('termsLineAdd');

	function nextIndex() {
		const rows = list.querySelectorAll('.terms-line-row');
		return rows.length;
	}

	function bindRemove(row) {
		row.querySelector('.terms-line-remove').addEventListener('click', function () {
			row.remove();
			reindex();
		});
	}

	function reindex() {
		list.querySelectorAll('.terms-line-row').forEach(function (row, i) {
			row.dataset.index = i;
			row.querySelectorAll('input').forEach(function (inp) {
				inp.name = inp.name.replace(/terms_lines\[\d+\]/, 'terms_lines[' + i + ']');
			});
		});
	}

	// Bind remove on existing rows
	list.querySelectorAll('.terms-line-row').forEach(bindRemove);

	addBtn.addEventListener('click', function () {
		const i   = nextIndex();
		const row = document.createElement('div');
		row.className  = 'terms-line-row d-flex gap-2 mb-2 align-items-start';
		row.dataset.index = i;
		row.innerHTML  = `
			<input type="text"
				class="form-control form-control-sm"
				style="width:170px;flex-shrink:0;"
				name="terms_lines[${i}][title]"
				placeholder="Title (optional)">
			<input type="text"
				class="form-control form-control-sm flex-grow-1"
				name="terms_lines[${i}][description]"
				placeholder="Description">
			<button type="button" class="btn btn-outline-danger btn-sm terms-line-remove" title="Remove line">&times;</button>
		`;
		bindRemove(row);
		list.appendChild(row);
		row.querySelector('input').focus();
	});
}());
</script>
