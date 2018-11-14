<?php

namespace SuluArticlesPublisherBundle\Services;

use ONGR\ElasticsearchBundle\Service\Manager;
use Sulu\Bundle\ArticleBundle\Admin\ArticleAdmin;
use Sulu\Bundle\ArticleBundle\Document\ArticleDocument;
use Sulu\Bundle\ArticleBundle\Document\Index\DocumentFactory;
use Sulu\Bundle\ArticleBundle\Metadata\ArticleViewDocumentIdTrait;
use Sulu\Component\DocumentManager\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\IdsQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;

class ArticlesPublisherManager
{
    const DOCUMENT_TYPE = 'article';

    private $container;
    private $documentManager;
    private $documentFactory;

    use ArticleViewDocumentIdTrait;

    public function __construct
    (
        ContainerInterface $container,
        DocumentManager $documentManager,
        DocumentFactory $documentFactory
    )
    {
        $this->container = $container;
        $this->documentManager = $documentManager;
        $this->documentFactory = $documentFactory;
    }

    /**
     * @return void
     * @throws \Sulu\Component\DocumentManager\Exception\DocumentManagerException
     */
    public function publishArticles()
    {
        $locale = 'en';

        /** @var Manager $manager */
        $manager = $this->container->get('es.manager.default');

        $repository = $manager->getRepository($this->container->get('sulu_article.view_document.factory')->getClass('article'));
        $search = $repository->createSearch();


        if (null !== $locale) {
            $search->addQuery(new TermQuery('locale', $locale));
        }

        if (count($ids = array_filter(explode(',', '')))) {
            $search->addQuery(new IdsQuery($this->getViewDocumentIds($ids, $locale)));
            $limit = count($ids);
        }

        if (null === $search->getQueries()) {
            $search->addQuery(new MatchAllQuery());
        }

        $articles = [];
        $searchResult = $repository->findDocuments($search);
        foreach ($searchResult as $document) {
            if (false !== ($index = array_search($document->getUuid(), $ids))) {
                $articles[$index] = $document;
            } else {
                $articles[] = $document;
            }
        }

        usort($articles, function ($a, $b) {
            /**
             * @var ArticleDocument $a
             * @var ArticleDocument $b
             */
            return strtotime($a->getPublished()->format('Y-m-d H:i:s')) + strtotime($b->getPublished()->format('Y-m-d H:i:s'));
        });

        foreach ($articles as $article) {

            $action = "publish";

            $document = $this->documentManager->find(
                $article->getUuid(),
                $locale,
                [
                    'load_ghost_content' => false,
                    'load_shadow_content' => false,
                ]
            );

            $this->handleActionParameter($action, $document, $locale);
            $this->documentManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityContext()
    {
        return ArticleAdmin::SECURITY_CONTEXT;
    }

    /**
     * Delegates actions by given actionParameter, which can be retrieved from the request.
     *
     * @param string $actionParameter
     * @param object $document
     * @param string $locale
     */
    private function handleActionParameter($actionParameter, $document, $locale)
    {
        switch ($actionParameter) {
            case 'publish':
                $this->documentManager->publish($document, $locale);
                break;
        }
    }
}