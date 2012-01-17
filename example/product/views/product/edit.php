<style type="text/css">
	fieldset {
		width: 300px;
	}
</style>
<div class="form">
<?php $form = $this->beginWidget('WForm', array('htmlOptions' => array('enctype'=>'multipart/form-data'))); ?>
	<fieldset>
		<legend>Product</legend>
		<div class="row">
			<?php echo $form->labelEx($product, 'name'); ?>
			<?php echo $form->textField($product, 'name'); ?>
			<?php echo $form->error($product, 'name'); ?>
		</div>

		<div class="row">
			<?php echo $form->labelEx($product, 'price'); ?>
			<?php echo $form->textField($product, 'price'); ?>
			<?php echo $form->error($product, 'price'); ?>
		</div>
	</fieldset>

	<fieldset>
		<legend>Category</legend>
		<div class="row">
			<?php echo $form->labelEx($product, 'category_id'); ?>
			<?php echo $form->dropDownList($product, 'category_id',
				array_merge(CHtml::listData(Category::model()->findAll(), 'id', 'name'), array('' => 'new category'))
			, array('empty' => 'none')) ?>
			<?php echo $form->error($product, 'category_id'); ?>

			<div style="display:none;" id="other-category">
				<?php echo $form->labelEx($product, 'category.name'); ?>
				<?php echo $form->textField($product, 'category.name', array('disabled' => 'disabled')); ?>
				<?php echo $form->error($product, 'category.name'); ?>
			</div>

		</div>
	</fieldset>

	<fieldset>
		<legend>Tags</legend>
		<div class="row">
			<?php if ($tags): ?>
				<ul class="tag-list">
					<?php foreach ($tags as $index => $tag): ?>
						<li>
							<label>
								<?php echo $tag->name ?>
								<?php echo $form->checkBox($product, "tags.{$index}.id", array('value' => $tag->id, 'uncheckValue' => null)) ?>
							</label>
						</li>
					<?php endforeach ?>
				</ul>
			<?php endif; ?>

			<ul class="tags">
				<?php if ($product->tags): ?>
				<?php foreach ($product->tags as $index => $tag): ?>
					<?php if ($tag->isNewRecord): ?>
						<li>
							<?php echo $form->textField($product, "tags.$index.name") ?>
							<?php echo $form->error($product, "tags.$index.name") ?>
							<a class="delete" href="#">delete</a>
						</li>
					<?php endif; ?>
				<?php endforeach ?>
				<?php endif; ?>
				<li style="display: none;" class="template" id="product-tag">
					<?php echo $form->textField($product, 'tags..name') ?>
				</li>
			</ul>
			<a id="add-tag" href="#">add</a>

		</div>
	</fieldset>

	<fieldset>
		<legend>Images</legend>
		<ul class="images">
			<?php if ($product->images): ?>
				<?php foreach ($product->images as $index => $image): ?>
					<?php if (!empty($image->file_origin)):?>
						<?php echo $form->hiddenField($product, "images.{$index}.object_type") ?>
						<?php echo $form->hiddenField($product, "images.{$index}.id") ?>
						<?php echo $form->hiddenField($product, "images.{$index}.file") ?>
						<?php echo $form->hiddenField($product, "images.{$index}.file_origin") ?>
						<?php echo $form->hiddenField($product, "images.{$index}.tempFile") ?>
						<?php echo CHtml::link($image->file_origin, $image->fileUrl) ?>
						<a href="#" class="delete"">Delete</a>
					<?php endif; ?>
				<?php endforeach ?>
			<?php endif; ?>
			<li style="display: none;" class="template" id="product-image">
				<?php echo $form->hiddenField($product, 'images..object_type', array('value' => Attachment::OBJECT_TYPE_PRODUCT_IMAGE)) ?>
				<?php echo $form->fileField($product, 'images..file') ?>
			</li>
		</ul>
		<a id="add-image" href="#">add</a>

	</fieldset>

	<fieldset>
		<legend>Certificate</legend>
		<div class="row">
			<?php echo $form->labelEx($product, 'certificate.name'); ?>
			<?php echo $form->textField($product, 'certificate.name'); ?>
			<?php echo $form->error($product, 'certificate.name'); ?>
			<?php if (!empty($product->certificate->image->file_origin)): ?>
				<?php echo $form->hiddenField($product, "certificate.image.object_type") ?>
				<?php echo $form->hiddenField($product, "certificate.image.id") ?>
				<?php echo $form->hiddenField($product, "certificate.image.file") ?>
				<?php echo $form->hiddenField($product, "certificate.image.file_origin") ?>
				<?php echo $form->hiddenField($product, "certificate.image.tempFile") ?>
				<?php echo CHtml::link($product->certificate->image->file_origin, $product->certificate->image->fileUrl) ?>
				<a href="#" class="delete"">Delete</a>
			<?php else: ?>
				<?php echo $form->hiddenField($product, 'certificate.image.object_type', array('value' => Attachment::OBJECT_TYPE_CERTIFICATE)) ?>
				<?php echo $form->fileField($product, 'certificate.image.file') ?>
			<?php endif; ?>
		</div>
	</fieldset>

	<fieldset>
		<legend>Description</legend>
		<div class="row">
			<?php echo $form->labelEx($product, 'description.color'); ?>
			<?php echo $form->textField($product, 'description.color'); ?>
			<?php echo $form->error($product, 'description.color'); ?>
		</div>
		<div class="row">
			<?php echo $form->labelEx($product, 'description.size'); ?>
			<?php echo $form->textField($product, 'description.size'); ?>
			<?php echo $form->error($product, 'description.size'); ?>
		</div>
	</fieldset>

	<div class="row submit">
		<?php echo CHtml::submitButton('Save'); ?>
	</div>

<?php $this->endWidget(); ?>
</div>

<script type="text/javascript">

	$(document).ready(function(){
		$('#ProductForm_category_id').change(function(){
			if (this.options.length == (this.selectedIndex + 1)) {
			    $('#other-category')
			    	.find('input')
			    		.removeAttr('disabled')
			    	.end()
			    .show();
			} else {
				$('#other-category')
					.find('input')
						.attr('disabled', 'disabled')
					.end()
				.hide();
			}
		});

		$('.tags').multiplyForms({
			addLink: '#add-tag'
		});

		$('.images').multiplyForms({
			addLink: '#add-image'
		});

	});

</script>
