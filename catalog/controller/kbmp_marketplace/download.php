<?php

class ControllerKbmpmarketplaceDownload extends Controller {

    private $error = array();

    public function __construct($params) {
        parent::__construct($params);
        $this->load->model('kbmp_marketplace/kbmp_marketplace');
        $this->model_kbmp_marketplace_kbmp_marketplace->getSellerLoggedStatus($this->url->link('kbmp_marketplace/download', '', true));
    }

    public function index() {
        $this->load->language('kbmp_marketplace/download');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('kbmp_marketplace/download');
        $this->getList();
    }

    public function add() {
        $this->load->language('kbmp_marketplace/download');
        $this->load->language('kbmp_marketplace/common');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('kbmp_marketplace/download');
        $this->load->model('kbmp_marketplace/kbmp_marketplace');
        $sellerId = $this->model_kbmp_marketplace_kbmp_marketplace->getSellerByCustomerId();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_kbmp_marketplace_download->addDownload($this->request->post, $sellerId['seller_id']);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('kbmp_marketplace/download', $url, true));
        }

        $this->getForm();
    }

    public function edit() {
        $this->load->language('kbmp_marketplace/download');
        $this->load->language('kbmp_marketplace/common');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('kbmp_marketplace/download');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_kbmp_marketplace_download->editDownload($this->request->get['download_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('kbmp_marketplace/download', $url, true));
        }

        $this->getForm();
    }

    public function delete() {
        $this->load->language('kbmp_marketplace/download');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('kbmp_marketplace/download');
        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $download_id) {
                $this->model_kbmp_marketplace_download->deleteDownload($download_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('kbmp_marketplace/download', $url, true));
        }

        $this->getList();
    }

    protected function getList() {
        $this->load->language('kbmp_marketplace/download');
        $this->load->language('kbmp_marketplace/common');
        $this->load->model('kbmp_marketplace/kbmp_marketplace');
        $data = array();

        $data['footer'] = $this->load->view('kbmp_marketplace/footer', $data);
        $data['header'] = $this->load->controller('kbmp_marketplace/header');

        $data['home_link'] = $this->url->link('common/home');
        $data['account_link'] = $this->url->link('account/account', '', true);
        $data['logout_link'] = $this->url->link('account/logout', '', true);
        $data['text_my_account1'] = $this->language->get('text_my_account1');
        $data['text_logout'] = $this->language->get('text_logout');
        $data['error_account_warning'] = $this->language->get('error_account_warning');
        $data['text_account_warning'] = $this->language->get('text_account_warning');

        $sellerId = $this->model_kbmp_marketplace_kbmp_marketplace->getSellerByCustomerId();
        $data['seller_details'] = $sellerId;

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'dd.name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['add'] = $this->url->link('kbmp_marketplace/download/add', $url, true);
        $data['delete'] = $this->url->link('kbmp_marketplace/download/delete', $url, true);

        $data['downloads'] = array();

        $filter_data = array(
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $results = $this->model_kbmp_marketplace_download->getDownloads($filter_data, $sellerId['seller_id']);

        foreach ($results as $result) {
            $data['downloads'][] = array(
                'download_id' => $result['download_id'],
                'name' => $result['name'],
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'edit' => $this->url->link('kbmp_marketplace/download/edit', '&download_id=' . $result['download_id'] . $url, true)
            );
        }
        $download_total = count($data['downloads']);
        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_list'] = $this->language->get('text_list');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');

        $data['column_name'] = $this->language->get('column_name');
        $data['column_date_added'] = $this->language->get('column_date_added');
        $data['column_action'] = $this->language->get('column_action');

        $data['button_add'] = $this->language->get('button_add');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array) $this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_name'] = $this->url->link('kbmp_marketplace/download', '&sort=dd.name' . $url, true);
        $data['sort_date_added'] = $this->url->link('kbmp_marketplace/download', '&sort=d.date_added' . $url, true);

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $download_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('kbmp_marketplace/download', $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($download_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($download_total - $this->config->get('config_limit_admin'))) ? $download_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $download_total, ceil($download_total / $this->config->get('config_limit_admin')));

        $data['sort'] = $sort;
        $data['order'] = $order;

        $this->response->setOutput($this->load->view('kbmp_marketplace/download_list', $data));
    }

    protected function getForm() {
        $this->load->language('kbmp_marketplace/download');
        $this->load->model('kbmp_marketplace/kbmp_marketplace');
        $data = array();

        $data['footer'] = $this->load->view('kbmp_marketplace/footer', $data);
        $data['header'] = $this->load->controller('kbmp_marketplace/header');
        $data['error_account_warning'] = $this->language->get('error_account_warning');
        $data['text_account_warning'] = $this->language->get('text_account_warning');

        $data['home_link'] = $this->url->link('common/home');
        $data['account_link'] = $this->url->link('account/account', '', true);
        $data['logout_link'] = $this->url->link('account/logout', '', true);
        $data['text_my_account1'] = $this->language->get('text_my_account1');
        $data['text_logout'] = $this->language->get('text_logout');

        $sellerId = $this->model_kbmp_marketplace_kbmp_marketplace->getSellerByCustomerId();
        $data['seller_details'] = $sellerId;

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_form'] = !isset($this->request->get['download_id']) ? $this->language->get('text_add') : $this->language->get('text_edit_download');
        $data['text_loading'] = $this->language->get('text_loading');

        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_filename'] = $this->language->get('entry_filename');
        $data['entry_mask'] = $this->language->get('entry_mask');

        $data['help_filename'] = $this->language->get('help_filename');
        $data['help_mask'] = $this->language->get('help_mask');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_upload'] = $this->language->get('button_upload');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = array();
        }

        if (isset($this->error['filename'])) {
            $data['error_filename'] = $this->error['filename'];
        } else {
            $data['error_filename'] = '';
        }

        if (isset($this->error['mask'])) {
            $data['error_mask'] = $this->error['mask'];
        } else {
            $data['error_mask'] = '';
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        if (!isset($this->request->get['download_id'])) {
            $data['action'] = $this->url->link('kbmp_marketplace/download/add', $url, true);
        } else {
            $data['action'] = $this->url->link('kbmp_marketplace/download/edit', '&download_id=' . $this->request->get['download_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('kbmp_marketplace/download', $url, true);

        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->get['download_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $download_info = $this->model_kbmp_marketplace_download->getDownload($this->request->get['download_id']);
        }

        if (isset($this->request->get['download_id'])) {
            $data['download_id'] = $this->request->get['download_id'];
        } else {
            $data['download_id'] = 0;
        }

        if (isset($this->request->post['download_description'])) {
            $data['download_description'] = $this->request->post['download_description'];
        } elseif (isset($this->request->get['download_id'])) {
            $data['download_description'] = $this->model_kbmp_marketplace_download->getDownloadDescriptions($this->request->get['download_id']);
        } else {
            $data['download_description'] = array();
        }

        if (isset($this->request->post['filename'])) {
            $data['filename'] = $this->request->post['filename'];
        } elseif (!empty($download_info)) {
            $data['filename'] = $download_info['filename'];
        } else {
            $data['filename'] = '';
        }

        if (isset($this->request->post['mask'])) {
            $data['mask'] = $this->request->post['mask'];
        } elseif (!empty($download_info)) {
            $data['mask'] = $download_info['mask'];
        } else {
            $data['mask'] = '';
        }

        $this->response->setOutput($this->load->view('kbmp_marketplace/download_form', $data));
    }

    protected function validateForm() {

        foreach ($this->request->post['download_description'] as $language_id => $value) {
            if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 64)) {
                $this->error['name'][$language_id] = $this->language->get('error_name');
            }
        }

        if ((utf8_strlen($this->request->post['filename']) < 3) || (utf8_strlen($this->request->post['filename']) > 128)) {
            $this->error['filename'] = $this->language->get('error_filename');
        }

        if (!is_file(DIR_DOWNLOAD . $this->request->post['filename'])) {
            $this->error['filename'] = $this->language->get('error_exists');
        }

        if ((utf8_strlen($this->request->post['mask']) < 3) || (utf8_strlen($this->request->post['mask']) > 128)) {
            $this->error['mask'] = $this->language->get('error_mask');
        }

        return !$this->error;
    }

    protected function validateDelete() {

        $this->load->adminmodel('catalog/product');

        foreach ($this->request->post['selected'] as $download_id) {
            $product_total = $this->model_catalog_product->getTotalProductsByDownloadId($download_id);

            if ($product_total) {
                $this->error['warning'] = sprintf($this->language->get('error_product'), $product_total);
                $this->session->data['error_warning'] = sprintf($this->language->get('error_product'), $product_total);
            }
        }
        return !$this->error;
    }

    public function upload() {
        $this->load->language('kbmp_marketplace/download');

        $json = array();

        if (!$json) {
            if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
                // Sanitize the filename
                $filename = basename(html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8'));

                // Validate the filename length
                if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 128)) {
                    $json['error'] = $this->language->get('error_filename');
                }

                // Allowed file extension types
                $allowed = array();
                $extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));

                $filetypes = explode("\n", $extension_allowed);

                foreach ($filetypes as $filetype) {
                    $allowed[] = trim($filetype);
                }

                if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
                    $json['error'] = $this->language->get('error_filetype');
                }

                // Allowed file mime types
                $allowed = array();

                $mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

                $filetypes = explode("\n", $mime_allowed);

                foreach ($filetypes as $filetype) {
                    $allowed[] = trim($filetype);
                }

                if (!in_array($this->request->files['file']['type'], $allowed)) {
                    $json['error'] = $this->language->get('error_filetype');
                }

                // Check to see if any PHP files are trying to be uploaded
                $content = file_get_contents($this->request->files['file']['tmp_name']);

                if (preg_match('/\<\?php/i', $content)) {
                    $json['error'] = $this->language->get('error_filetype');
                }

                // Return any upload error
                if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                    $json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
                }
            } else {
                $json['error'] = $this->language->get('error_upload');
            }
        }

        if (!$json) {
            $file = $filename . '.' . token(32);

            move_uploaded_file($this->request->files['file']['tmp_name'], DIR_DOWNLOAD . $file);

            $json['filename'] = $file;
            $json['mask'] = $filename;

            $json['success'] = $this->language->get('text_upload');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}
