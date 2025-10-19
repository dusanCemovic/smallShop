<?php

namespace App\Controllers;

use App\Models\Article;

class ArticlesController extends BaseController
{
    protected $model; // article model

    public function __construct()
    {
        // load model for article
        $this->model = new Article();
    }

    /**
     * Load all articles
     * @return void
     * @throws \Exception
     */
    public function index()
    {
        try {
            $articles = $this->model->all();
            $this->render('articles/list', ['articles' => $articles]);
        } catch (\Exception $e) {
            // we can put those things in logs and then redirect
            $this->logError($e->getMessage());
            $this->redirect('/?route=articles.index');
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function delete()
    {
        try {
            $id = (int)$_POST['id'] ?? null;
            $this->model->delete($id);
            $this->redirect('/?route=articles.index');
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            $this->redirect('/?route=articles.index');
        }
    }

    /** This is for creating and editing/updating
     * @param $id
     * @return void
     * @throws \Exception
     */
    public function form($id = null)
    {
        try {
            $params = [];
            if ($id === null) {
                $params['action'] = 'create';
            } else {
                $params['action'] = 'update';
                $params['article'] = $this->model->findOne($id);
                if ($params['article'] === false) {
                    throw new \Exception("Article with 'id': " . $id ." not found");
                }
            }

            $this->render('articles/form', $params);
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            $this->redirect('/?route=articles.index');
        }
    }

    /**
     * If it is edit, just use same method and send id
     * @return void
     */
    public function editForm()
    {
        $this->form((int) $_GET['id']);
    }

    /**
     * Create new Article submit
     * @return void
     * @throws \Exception
     */
    public function create()
    {
        $args = $this->cleanArguments();
        $errors = $this->checkForm($args);

        try {

            // check if frontend errors occur, or continue
            if (!empty($errors)) {
                $this->render('articles/form',
                    ['action' => 'create', 'errors' => $errors, 'old' => $args]);
                return;
            }

            $this->model->create($args);
            $this->redirect('/?route=articles.index');

        } catch (\Exception $e) {
            // this is example what we can do. If error comes, we can send also customer some info
            $this->logError($e->getMessage());
            $errors['message'] = 'Please contact administrator or try later';
            $this->render('articles/form',
                ['action' => 'create', 'errors' => $errors, 'old' => $args]);
        }
    }

    /**
     * Edit Article submit
     * @return void
     * @throws \Exception
     */
    public function update()
    {
        $args = $this->cleanArguments();
        $errors = $this->checkForm($args);

        try {

            // check if frontend errors occur, or continue
            if (!empty($errors)) {
                $this->render('articles/form',
                    ['action' => 'update', 'errors' => $errors, 'old' => $args]);
                return;
            }

            $id = $_POST['id'] ?? null;
            $this->model->update( (int) $id, $args);
            $this->redirect('/?route=articles.index');

        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            $this->redirect('/?route=articles.index');
        }


    }

    /**
     * This checking are just simple for now. We can extend this to have more details
     * New frameworks have more sofisticied validation
     * @return array
     */
    private function cleanArguments()
    {
        $cleanedArgs['name'] = trim($_POST['name'] ?? '');
        $cleanedArgs['price'] = $_POST['price'] ?? null;
        $cleanedArgs['supplier_email'] = $_POST['supplier_email'] ?? null;
        $cleanedArgs['description'] = $_POST['description'] ?? null;

        return $cleanedArgs;
    }

    /**
     * Return errors to view or allow to proceed
     * @param $args
     * @return array
     */
    private function checkForm($args)
    {
        // this checking are just simple for now. We can extend this to have more details

        $errors = [];
        if ($args['name'] === '') {
            $errors[] = 'Name required';
        }

        if (!is_numeric($args['price'])) {
            $errors[] = 'Price must be numeric';
        }

        return $errors;
    }
}

