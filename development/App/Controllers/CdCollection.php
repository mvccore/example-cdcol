<?php

class App_Controllers_CdCollection extends App_Controllers_Base
{
	/** @var App_Models_Album */
	protected $album;
    /**
	 * Initialize this controller, before prdispatching and before every action 
	 * executing in current controller. This method is template method - so 
	 * it's necessary to call parent method at the beginning.
	 */
	public function Init(){
		parent::Init();
		// if user is not authorized, redirect to homepage and exit
		if (!$this->user) {
			self::Redirect($this->Url(
				'Default:Default',
				array('sourceUrl' => urlencode($this->request->Referer))
			));
		}
	}
    /**
     * Pre execute every action in current controller. This method 
	 * is template method - so it's necessary to call parent method at the beginning.
     */
    public function PreDispatch() {
        parent::PreDispatch();
		// if there is any 'id' param in $_GET or $_POST,
		// try to load album model instance from database
		$id = $this->GetParam("id", "0-9");
		if (strlen($id) > 0) {
			$this->album = App_Models_Album::GetById($id);
			if (!$this->album) $this->renderNotFound();
		}
    }
    /**
	 * Load all album items, create virtual delete form
	 * to initialize and manage CSRF tokens only once, not
	 * for every album row and add supporting js file
	 * to initialize javascript in delete post forms
	 * created multiple times in view only.
	 * @return void
     */
    public function DefaultAction () {
		$this->view->Title = 'CD Collection';
		$this->view->Albums = App_Models_Album::GetAll();
		/** @var $abstractForm SimpleForm */
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
			->SetDefaults($this->album->GetValues(), TRUE);
    }
    /**
     * Submit action data fro create and edit form.
	 * @return void
     */
    public function SubmitAction () {
		$detailForm = $this->getCreateEditForm();
		if (!$this->album) {
			$this->album = new App_Models_Album();
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
	 * are the same as tokens in session, tokens are managed 
	 * by virtual delete form, initialized only once, not for all album rows.
	 * @return void
     */
    public function DeleteAction () {
		if ($this->getVirtualDeleteForm()->ValidateCsrf($_POST)) {
			$this->album->Delete();
		}
		self::Redirect($this->Url(':Default'));
    }
	/**
	 * Create form for create album and edit album
	 * @return SimpleForm
	 */
	protected function getCreateEditForm ($editForm = TRUE) {
		$form = (new SimpleForm($this))
			->SetId('detail')
			->SetMethod(SimpleForm::METHOD_POST)
			->SetAction($this->Url(':Submit'))
			->SetSuccessUrl($this->Url(':Default', array('absolute' => TRUE)))
			->SetFieldsDefaultRenderMode(
				SimpleForm::FIELD_RENDER_MODE_LABEL_AROUND
			);
		if ($editForm) {
			$id = (new SimpleForm_Hidden)
				->SetName('id')
				->AddValidators('NumberField');
			$form->AddField($id);
		}
		$title = (new SimpleForm_Text)
			->SetName('title')
			->SetLabel('Title:')
			->SetSize(200)
			->SetRequired()
			->SetAutocomplete('off');
		$interpret = (new SimpleForm_Text)
			->SetName('interpret')
			->SetLabel('Interpret:')
			->SetSize(200)
			->SetRequired()
			->SetAutocomplete('off');
		$year = (new SimpleForm_Number)
			->SetName('year')
			->SetLabel('Year:')
			->SetSize(4);
		$send = (new SimpleForm_SubmitButton)
			->SetName('send')
			->SetCssClasses('button-green')
			->SetValue('<span><b>Save</b></span>');
		return $form->AddFields($title, $interpret, $year, $send);
	}
	/**
	 * Create empty form where to store CSRF tokens
	 * @return SimpleForm
	 */
	protected function getVirtualDeleteForm () {
		return (new SimpleForm($this))
			->SetId('delete')
			// set error url, where to redirect if CSRF 
			// are wrong, see App_Controller_Base::Init()
			->SetErrorUrl(
				$this->Url(':Default', array('absolute' => TRUE))
			);
	}
}