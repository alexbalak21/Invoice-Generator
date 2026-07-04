<div class="card shadow-sm border-0 mb-4">
	<div class="card-body p-4">
		<h2 class="h5 mb-4">Customer</h2>
		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label">Customer name</label>
				<input class="form-control" type="text" name="customer[name]" value="<?= h($customer['name'] ?? '') ?>" required>
			</div>
			<div class="col-md-6">
				<label class="form-label">Company</label>
				<input class="form-control" type="text" name="customer[company]" value="<?= h($customer['company'] ?? '') ?>">
			</div>
			<div class="col-md-12">
				<label class="form-label">Department</label>
				<input class="form-control" type="text" name="customer[department]" value="<?= h($customer['department'] ?? '') ?>">
			</div>
			<div class="col-md-12">
				<label class="form-label">Street</label>
				<input class="form-control" type="text" name="customer[street]" value="<?= h($customer['street'] ?? '') ?>" required>
			</div>
			<div class="col-md-4">
				<label class="form-label">City</label>
				<input class="form-control" type="text" name="customer[city]" value="<?= h($customer['city'] ?? '') ?>" required>
			</div>
			<div class="col-md-4">
				<label class="form-label">ZIP</label>
				<input class="form-control" type="text" name="customer[zip]" value="<?= h($customer['zip'] ?? '') ?>" required>
			</div>
			<div class="col-md-4">
				<label class="form-label">Country</label>
				<input class="form-control" type="text" name="customer[country]" value="<?= h($customer['country'] ?? '') ?>" required>
			</div>
			<div class="col-md-6">
				<label class="form-label">Email</label>
				<input class="form-control" type="email" name="customer[email]" value="<?= h($customer['email'] ?? '') ?>" required>
			</div>
			<div class="col-md-6">
				<label class="form-label">Phone</label>
				<input class="form-control" type="text" name="customer[phone]" value="<?= h($customer['phone'] ?? '') ?>">
			</div>
			<div class="col-md-12">
				<label class="form-label">VAT number</label>
				<input class="form-control" type="text" name="customer[vat_number]" value="<?= h($customer['vat_number'] ?? '') ?>">
			</div>
		</div>
	</div>
</div>
