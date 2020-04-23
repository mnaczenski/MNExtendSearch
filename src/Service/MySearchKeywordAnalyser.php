<?php
namespace MNExtendSearch\Service;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SearchKeyword\AnalyzedKeywordCollection;
use Shopware\Core\Content\Product\SearchKeyword\AnalyzedKeyword;
use Shopware\Core\Content\Product\SearchKeyword\ProductSearchKeywordAnalyzerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Term\TokenizerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Framework\Context;

class MySearchKeywordAnalyser implements ProductSearchKeywordAnalyzerInterface
{
    /**
     * @var ProductSearchKeywordAnalyzerInterface
     */
    private $coreAnalyzer;


    /**
     * @var TokenizerInterface
     */
    private $tokenizer;

    /**
     * @var EntityRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $optionRepository;


    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(ProductSearchKeywordAnalyzerInterface $coreAnalyzer, TokenizerInterface $tokenizer, SystemConfigService $systemConfigService, EntityRepositoryInterface $categoryRepository, EntityRepositoryInterface $optionRepository)
    {
        $this->coreAnalyzer = $coreAnalyzer;
        $this->tokenizer = $tokenizer;
        $this->systemConfigService = $systemConfigService;
        $this->categoryRepository = $categoryRepository;
        $this->optionRepository = $optionRepository;
    }
    public function analyze(ProductEntity $product, Context $context): AnalyzedKeywordCollection
    {

        $keywords = $this->coreAnalyzer->analyze($product, $context);

        if ($this->systemConfigService->get('MNExtendSearch.config.description') == true) {
            $description = $product->getTranslation('description');

            $ranking = $this->systemConfigService->get('MNExtendSearch.config.rankingdescription');

            if ($description) {
                $tokens = $this->tokenizer->tokenize((string) $description);
                foreach ($tokens as $token) {
                    $keywords->add(new AnalyzedKeyword((string) $token, $ranking));
                }
            }
        }

        if ($this->systemConfigService->get('MNExtendSearch.config.metadescription') == true) {
            $metadescription = $product->getTranslation('metaDescription');

            $ranking = $this->systemConfigService->get('MNExtendSearch.config.rankingmetadescription');

            if ($metadescription) {
                $tokens = $this->tokenizer->tokenize((string) $metadescription);
                foreach ($tokens as $token) {
                    $keywords->add(new AnalyzedKeyword((string) $token, $ranking));
                }
            }
        }

        if ($this->systemConfigService->get('MNExtendSearch.config.metatitle') == true) {
            $metatitle = $product->getTranslation('metaTitle');

            $ranking = $this->systemConfigService->get('MNExtendSearch.config.rankingmetatitle');

            if ($metatitle) {
                $tokens = $this->tokenizer->tokenize((string) $metatitle);
                foreach ($tokens as $token) {
                    $keywords->add(new AnalyzedKeyword((string) $token, $ranking));
                }
            }
        }

        if ($this->systemConfigService->get('MNExtendSearch.config.categories') == true) {

            $categories = $product->getCategoryTree();
            $categories = $this->categoryRepository->search(
                new Criteria($categories),
                \Shopware\Core\Framework\Context::createDefaultContext()
            );

            $ranking = $this->systemConfigService->get('MNExtendSearch.config.rankingcategories');

            if ($categories) {
                foreach ($categories as $category) {
                    $categoryName = $category->getTranslation('name');
                    $keywords->add(new AnalyzedKeyword((string) $categoryName, $ranking));
                }
            }
        }

        if ($this->systemConfigService->get('MNExtendSearch.config.properties') == true) {

            $properties = $product->getPropertyIds();

            $properties = $this->optionRepository->search(
                new Criteria($properties),
                \Shopware\Core\Framework\Context::createDefaultContext()
            );


            $ranking = $this->systemConfigService->get('MNExtendSearch.config.rankingproperties');

            if ($properties) {
                foreach ($properties as $property) {
                    $propertyName = $property->getTranslation('name');
                    $keywords->add(new AnalyzedKeyword((string) $propertyName, $ranking));
                }
            }
        }

        if ($this->systemConfigService->get('MNExtendSearch.config.productname') == true) {

            $productname = $product->getTranslation('name');

            $ranking = $this->systemConfigService->get('MNExtendSearch.config.rankingproductname');

            if ($productname) {
                $tokens = $this->tokenizer->tokenize((string) $productname);
                foreach ($tokens as $token) {
                    $keywords->add(new AnalyzedKeyword((string) $token, $ranking));
                }
            }
        }

        if ($this->systemConfigService->get('MNExtendSearch.config.productnumber') == true) {
            $ranking = $this->systemConfigService->get('MNExtendSearch.config.rankingproductnumber');
            $keywords->add(new AnalyzedKeyword($product->getProductNumber(), $ranking));
        }

        if ($this->systemConfigService->get('MNExtendSearch.config.manufacturername') == true) {

            $ranking = $this->systemConfigService->get('MNExtendSearch.config.rankingmanufacturername');

            if ($product->getManufacturer()) {
                $keywords->add(new AnalyzedKeyword((string) $product->getManufacturer()->getTranslation('name'), $ranking));
            }
        }

        if ($this->systemConfigService->get('MNExtendSearch.config.eans') == true) {

            $ranking = $this->systemConfigService->get('MNExtendSearch.config.rankingeans');
            
            if ($product->getEan()) {
                $keywords->add(new AnalyzedKeyword($product->getEan(), $ranking));
            }
        }

        if ($this->systemConfigService->get('MNExtendSearch.config.manufacturernumber') == true) {

            $ranking = $this->systemConfigService->get('MNExtendSearch.config.rankingmanufacturernumber');
            
            if ($product->getEan()) {
                $keywords->add(new AnalyzedKeyword($product->getManufacturerNumber(), 500));
            }
        }

        return $keywords;
    }
}