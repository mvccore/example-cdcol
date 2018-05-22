<?php

namespace App\Controllers;

use \App\Models,
	\MvcCore\Ext\Form;

class CdCollection extends Base
{
	/** @var \App\Models\Album */
	protected $album;

	/**
	 * Initialize this controller, before pre-dispatching and before every action
	 * executing in this controller. This method is template method - so
	 * it's necessary to call parent method first.
	 * @return void
	 */
	public function Init () {
		parent::Init();
		// if user is not authorized, redirect to homepage and exit
		if (!$this->user)
			self::Redirect($this->Url(
				'Index:Index', array('sourceUrl' => rawurlencode($this->request->GetFullUrl()))
			));
	}

	/**
	 * Pre execute every action in this controller. This method
	 * is template method - so it's necessary to call parent method first.
	 * @return void
	 */
	public function PreDispatch () {
		parent::PreDispatch();
		// if there is any 'id' param in $_GET or $_POST,
		// try to load album model instance from database
		$id = $this->GetParam("id", "0-9");
		if (strlen($id) > 0) {
			$this->album = Models\Album::GetById(intval($id));
			if (!$this->album) $this->renderNotFound();
		}
	}

	/**
	 * Load all album items, create empty form  to delete any item
	 * to generate and manage CSRF tokens (once only, not
	 * for every album row) and add supporting js file
	 * to initialize javascript in delete post forms
	 * created multiple times in view only.
	 * @return void
	 */
	public function IndexAction () {
		$this->view->Title = 'CD Collection';
		$this->view->Albums = Models\Album::GetAll();
		/** @var $abstractForm \MvcCore\Ext\Form */
		list($this->view->CsrfName, $this->view->CsrfValue)
			= $this->getVirtualDeleteForm()->SetUpCsrf();
		$this->view->Js('varFoot')
			->Prepend(self::$staticPath . '/js/List.js');
	}

	/**
	 * Create form for new album without hidden id input.
	 * @return void
	 */
	public function CreateAction () {
		$this->view->Title = 'New album';
		$this->view->DetailForm = $this->getCreateEditForm(FALSE);
	}

	/**
	 * Load previously saved album data,
	 * create edit form with hidden id input
	 * and set form defaults with album values.
	 * @return void
	 */
    public function EditAction () {
		$this->view->Title = 'Edit album - ' . $this->album->Title;
		$this->view->DetailForm = $this->getCreateEditForm(TRUE)
			->SetDefaults($this->album->GetValues(), TRUE, TRUE);
    }

	/**
	 * Handle create and edit action form submit.
	 * @return void
	 */
	public function SubmitAction () {
		$detailForm = $this->getCreateEditForm();
		if (!$this->album) {
			$this->album = new Models\Album();
			$detailForm->SetErrorUrl($this->Url(':Create', array('absolute' => TRUE)));
		} else {
			$detailForm->SetErrorUrl($this->Url(':Edit', array('id' => $this->album->Id, 'absolute' => TRUE)));
		}
		$detailForm->Submit();
		$detailForm->UnsetEmptyData();
		if ($detailForm->Result) {
			$this->album->SetUp($detailForm->Data, TRUE)->Save();
		}
		$detailForm->RedirectAfterSubmit();
	}

	/**
	 * Delete album by sended id param, if sended CSRF tokens
	 * are the same as CSRF tokens in session (tokens are managed
	 * by empty virtual delete form initialized once, not for all album rows).
	 * @return void
	 */
    public function DeleteAction () {
		if ($this->getVirtualDeleteForm()->ValidateCsrf($_POST)) {
			$this->album->Delete();
		}
		self::Redirect($this->Url(':Index'));
    }

	/**
	 * Create form instance to create new or edit existing album.
	 * @return \MvcCore\Ext\Form
	 */
	protected function getCreateEditForm ($editForm = TRUE) {
		$form = (new Form($this))
			->SetId('detail')
			->SetMethod(Form::METHOD_POST)
			->SetAction($this->Url(':Submit'))
			->SetSuccessUrl($this->Url(':Index', array('absolute' => TRUE)))
			->AddCssClass('theme')
			->SetFieldsDefaultRenderMode(
				Form::FIELD_RENDER_MODE_LABEL_AROUND
			);
		if ($editForm) {
			$id = (new Form\Hidden)
				->SetName('id')
				->AddValidators('NumberField');
			$form->AddField($id);
		}
		$title = (new Form\Text)
			->SetName('title')
			->SetLabel('Title:')
			->SetSize(200)
			->SetRequired()
			->SetAutocomplete('off');
		$interpret = (new Form\Text)
			->SetName('interpret')
			->SetLabel('Interpret:')
			->SetSize(200)
			->SetRequired()
			->SetAutocomplete('off');
		$year = (new Form\Number)
			->SetName('year')
			->SetLabel('Year:')
			->SetSize(4);
		$send = (new Form\SubmitButton)
			->SetName('send')
			->SetCssClasses('btn btn-large')
			->SetValue('Save');
		return $form->AddFields($title, $interpret, $year, $send);
	}

	/**
	 * Create empty form, where to store and manage CSRF 
	 * tokens for delete links in albums list.
	 * @return \MvcCore\Ext\Form
	 */
	protected function getVirtualDeleteForm () {
		return (new Form($this))
			->SetId('delete')
			// set error url, where to redirect if CSRF
			// are wrong, see App_Controller_Base::Init()
			->SetErrorUrl(
				$this->Url('Index:Index', array('absolute' => TRUE))
			);
	}
}
