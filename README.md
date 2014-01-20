Yii Composite Form Extension
==========================

The extension that can greatly simplify processing of complex forms with multiple relations.

[Weavora's](http://weavora.com) Git Repo - [https://github.com/weavora/wform](https://github.com/weavora/wform)

**Features**:

* Easy composite form processing
* Fast configuration
* Support of all standard relations: has_one, belongs_to, has_many and many_many

Configuration
-----

1) Download and unpack the source into the protected/extensions/ folder.

2) Below you can see the config settings for import:

```php
<?php
// main.php
return array(
	...
	'import' => array(
		...
		'ext.wform.*',
	),
	...
);
```

3) The extension requires changing CActiveRecord::onUnsafeAttribute. Here are a few options for that:

a) Extend all your models/forms from WActiveRecord instead of CActiveRecord

b) If you have already modified the class for active record, extend it from WActiveRecord or add the onUnsafeAttribute method:

```php
<?php
// protected/components/ActiveRecord.php

// extend from WActiveRecord
class ActiveRecord extends WActiveRecord
{
	// you custom code here (if reqired)
}

// or add onUnsafeAttribute method
class ActiveRecord extends CActiveRecord
{
	// you custom code here (if required)

	/**
	 * Raise onUnsafeAttribute event for active record
	 * Required for wform extension
	 *
	 * @param $name unsafe attribute name
	 * @param $value unsafe attribute value
	 */
	public function onUnsafeAttribute($name, $value)
	{
		$event = new CEvent($this, array('name' => $name, 'value' => $value));
		$this->raiseEvent('onUnsafeAttribute', $event);
		return parent::onUnsafeAttribute($name, $value);
	}
}
```

Usage
-----

1) Modify the model: define relations and attach behavior.
You can also create a separate class for the form extended from your model.

```php
<?php
class MyModel extends WActiveRecord {
	...
	public function relations()
	{
		return array(
			'hasOneRelation' => array(self::HAS_ONE, 'HasOneModel', 'my_model_fk_into_related_model'),
			'belongsToRelation' => array(self::BELONGS_TO, 'BelongsToModel', 'related_model_fk_into_my_model'),
			'hasManyRelation' => array(self::HAS_MANY, 'HasManyModel', 'my_model_fk_into_related_model'),
			'manyManyRelation' => array(self::MANY_MANY, 'ManyManyModel', 'linker(my_model_id,related_model_id)'),
		);
	}
	...
	public function behaviors() {
		return array(
			// attach wform behavior
			'wform' => array(
				'class' => 'ext.wform.WFormBehavior',
				// define relations which would be processed
				'relations' => array('hasOneRelation', 'belongsToRelation', 'hasManyRelation', 'manyManyRelation'),
			),
			// or you could allow to skip some relation saving if it was submitted empty
			'wform' => array(
				'class' => 'ext.wform.WFormBehavior',
				'relations' => array(
					'hasOneRelation' => array(
						'required' => true, // declare that a relation item should be valid (default for HAS_ONE: false)
						'cascadeDelete' => true, // declare if a relation item would be deleted during parent model deletion  (default for HAS_ONE: true)
					),
					'belongsToRelation' => array(
						'required' => true, // declare all relation items to be valid (default for BELONGS_TO: false)
					),
					'hasManyRelation' => array(
						'required' => true, // declare all relation items to be valid (default for HAS_MANY: false)
						'unsetInvalid' => true, // will unset invalid relation items during save or validate actions (default for HAS_MANY: false)
						'cascadeDelete' => true, // declare if relation items would be deleted during parent model deletion  (default for HAS_MANY: true)
					),
					'manyManyRelation' => array(
						'required' => true, // declare all relation items to be valid (default for MANY_MANY: false)
						'unsetInvalid' => true, // will unset invalid relation items during save or validate actions (default for MANY_MANY: false)
						'cascadeDelete' => true, // declare if db rows with a relation item link to model would be deleted during parent model deletion  (default for MANY_MANY: true)
					),
				),
			),
		);
	}
	...
}
```

2) Create an action to process the form.

```php
<?php
class MyController extends Controller {
	...
	// form creation & editing processed by a single action
	public function actionEdit($id = null)
	{
		$myModel = $id ? MyModel::model()->with('hasManyRelation','manyManyRelation')->findByPk($id) : new MyModel();
		if(Yii::app()->request->isPostRequest) {
			$myModel->attributes = Yii::app()->request->getPost('MyModel');
			if ($myModel->save()) {
				$this->redirect('some/page');
			}
		}
		$this->render('edit', array(
			'model' => $myModel
		));
	}

	// delete the model with relation to a single line of code :)
	public function actionDelete($id)
	{
		$myModel = MyModel::model()->findByPk($id);
		if(!empty($myModel)) {
			$myModel->delete();
		}
		$this->redirect('some/page');
	}
}
```

3) Include js/jquery.multiplyforms.js jquery plugin into your layout


4) Define the form using WForm instead of CActiveForm

```php
// protected/views/my/edit.php

<h1><?php echo ($model->isNewRecord ? "Create" : "Update " . $model->name);?></h1>
<?php $form = $this->beginWidget('WForm'); ?>

<!-- MyModel form fields -->
<div class="row">
	<?php echo $form->labelEx($model, 'name'); ?>
	<?php echo $form->textField($model, 'name'); ?>
	<?php echo $form->error($model, 'name'); ?>
</div>

<!-- fields of embeded forms -->

<!-- has_one relation -->
<div class="row">
	<?php echo $form->labelEx($model, 'hasOneRelation.name'); ?>
	<?php echo $form->textField($model, 'hasOneRelation.name'); ?>
	<?php echo $form->error($model, 'hasOneRelation.name'); ?>
</div>

<!-- belongs_to relation -->
<div class="row">
	<?php echo $form->labelEx($model, 'belongsToRelation.name'); ?>
	<?php echo $form->textField($model, 'belongsToRelation.name'); ?>
	<?php echo $form->error($model, 'belongsToRelation.name'); ?>
</div>

<!-- has_many relation -->
<div class="row hasManyRelation">
	<!-- exists items -->
	<?php if ($model->hasManyRelation): ?>
		<?php foreach ($model->hasManyRelation as $index => $item): ?>
			<div class="has-many-item">
				<?php if (!$item->isNewRecord): ?>
					<?php echo $form->hiddenField($model, "hasManyRelation.$index.id"); ?>
				<?php endif; ?>
				<?php echo $form->labelEx($model, "hasManyRelation.$index.text"); ?>
				<?php echo $form->textField($model, "hasManyRelation.$index.text"); ?>
				<?php echo $form->error($model, "hasManyRelation.$index.text"); ?>
				<a href="#" class="delete">Delete</a>
			</div>
		<?php endforeach ?>
	<?php endif; ?>

	<!-- create new items -->
	<div class="has-many-item just-empty-form-template-hasManyRelation">
		<?php echo $form->labelEx($model, "hasManyRelation..text"); ?>
		<?php echo $form->textField($model, "hasManyRelation..text"); ?>
		<?php echo $form->error($model, "hasManyRelation..text"); ?>
		<a href="#" class="delete">Delete</a>
	</div>

	<a href="#" class="add">Add more</a>
</div>

<!-- many_many relation -->
<div class="row manyManyRelation">
	<!-- exists items -->
	<?php if ($model->manyManyRelation): ?>
		<?php foreach ($model->manyManyRelation as $index => $item): ?>
			<div class="many-many-item">
				<?php if (!$item->isNewRecord): ?>
					<?php echo $form->hiddenField($model, "manyManyRelation.$index.id"); ?>
				<?php endif; ?>
				<?php echo $form->labelEx($model, "manyManyRelation.$index.note"); ?>
				<?php echo $form->textField($model, "manyManyRelation.$index.note"); ?>
				<?php echo $form->error($model, "manyManyRelation.$index.note"); ?>
				<a href="#" class="delete">Delete</a>
			</div>
		<?php endforeach ?>
	<?php endif; ?>

	<!-- create new items -->
	<div class="many-many-item just-empty-form-template-manyManyRelation">
		<?php echo $form->labelEx($model, "manyManyRelation..note"); ?>
		<?php echo $form->textField($model, "manyManyRelation..note"); ?>
		<?php echo $form->error($model, "manyManyRelation..note"); ?>
		<a href="#" class="delete">Delete</a>
	</div>

	<a href="#" class="add">Add more</a>
</div>

<div class="row buttons">
	<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
</div>

<?php $this->endWidget(); ?>

<script type="text/javascript">
	$(document).ready(function(){
		// init controls for multiply form
		$('.hasManyRelation').multiplyForms({
			embedClass: 'has-many-item',
			templateClass: 'just-empty-form-template-hasManyRelation'
		});

		$('.manyManyRelation').multiplyForms({
			embedClass: 'many-many-item',
			templateClass: 'just-empty-form-template-manyManyRelation',
			addLink: '.add',
			deleteLink: '.delete',
			mode: 'append', // could be also 'prepend'. Specify should new form put to top or bottom of list
			// apendTo: '.some-element', // Append to new item into .some-element inside .manyManyRelation
			// prependTo: '.some-element'
		})
		.on('multiplyForms.add', function(event, embedForm, multiplyFormInstance){})
		.on('multiplyForms.delete', function(event, embedForm, multiplyFormInstance){
			if (!confirm("Are you sure to delete this record?")) {
				event.preventDefault();
			}
		});


	});
</script>

```

Real Examples
-----

[product form example](https://github.com/weavora/wform/wiki/Example:-Product-form)
