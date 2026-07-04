<div class="card shadow-sm border-0">
	<div class="card-body p-4">
		<h2 class="h5 mb-3">Notes</h2>
		<div class="row g-3">
			<div class="col-12">
				<label class="form-label">Public notes</label>
				<textarea class="form-control" rows="4" name="notes[public]"><?= h($notes['public'] ?? '') ?></textarea>
			</div>
			<div class="col-12">
				<label class="form-label">Internal notes</label>
				<textarea class="form-control" rows="3" name="notes[internal]"><?= h($notes['internal'] ?? '') ?></textarea>
			</div>
			<div class="col-12">
				<label class="form-label">Terms &amp; Conditions</label>
				<textarea class="form-control" rows="4" name="terms"><?= h($terms) ?></textarea>
			</div>
			<div class="col-12">
				<label class="form-check-label">
					<input class="form-check-input me-2" type="checkbox" name="acceptance[enabled]" value="1" <?= ($type === 'quote' || !empty(($state['acceptance']['enabled'] ?? false))) ? 'checked' : '' ?>>
					Include acceptance text in the printed document
				</label>
			</div>
			<div class="col-12">
				<label class="form-label">Acceptance text</label>
				<textarea class="form-control" rows="2" name="acceptance[text]"><?= h($state['acceptance']['text'] ?? 'Quote received before execution, read and approved, agreed.') ?></textarea>
			</div>
		</div>
	</div>
</div>
