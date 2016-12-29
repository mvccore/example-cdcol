<?php

class App_Controllers_CdCollection extends App_Controllers_Base
{
	protected $album;
	public function Init(){
		parent::Init();
		if (!$this->user) {
			self::Redirect($this->Url('Default::Default'));
		}
	}
    public function PreDispatch() {
        parent::PreDispatch();
		$id = $this->GetParam("id", "0-9");
		if (strlen($id) > 0) {
			$this->album = App_Models_Album::GetById($id);
		}
    }
    public function DefaultAction () {
		$this->view->Title = 'CD collection';
		$this->view->Albums = App_Models_Album::GetAll();
		$this->view->Js('varFoot')->Prepend(self::$staticPath . 'js/List.js');
    }
    public function CreateAction () {
		$this->view->Title = 'New album';
		$this->view->Errors = $this->formErrors('cdcol');
		$this->view->Album = new App_Models_Album();
    }
    public function EditAction () {
		$this->view->Title = $this->album->Title;
		$this->view->Errors = $this->formErrors('cdcol');
		$this->view->Album = $this->album;
    }
    public function SubmitAction () {
		$result = FALSE;
        $allowedCharsChroup = 'a-zA-Z0-9 _\@\#\+\-\*\/\(\)\[\]\{\}\–\&';
		$id	= $this->GetParam("id", "0-9");
		if ($this->checkSessionHash()) {
			if (!$this->album) $this->album = new App_Models_Album();

			$interpret	= $this->GetParam('interpret', $allowedCharsChroup);
			$title		= $this->GetParam('title', $allowedCharsChroup);
			$year		= $this->GetParam('year', '0-9');

			if (!$interpret) {
				$this->formErrors('cdcol', "Interpret is required.");
			} else if (!$title) {
				$this->formErrors('cdcol', "Title is required.");
			} else {
				$this->album->Interpret	= $interpret;
				$this->album->Title		= $title;
				$this->album->Year		= $year;
				$this->album->Save();
				$result = TRUE;
			}
		}
		if ($result) {
			self::Redirect($this->Url('CdCollection::Default'));
		} else {
			self::Redirect($this->Url($id ? 'CdCollection::Edit' : 'CdCollection::Create'));
		}
    }
    public function DeleteAction () {
		if ($this->checkSessionHash()) {
			$this->album->Delete();
		}
		self::Redirect($this->Url('CdCollection::Default'));
    }
}