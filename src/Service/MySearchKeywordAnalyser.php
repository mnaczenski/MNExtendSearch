<?php
namespace MNExtendSearch\Service;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SearchKeyword\AnalyzedKeywordCollection;
use Shopware\Core\Content\Product\SearchKeyword\AnalyzedKeyword;
use Shopware\Core\Content\Product\SearchKeyword\ProductSearchKeywordAnalyzerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Term\TokenizerInterface;
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


    public function __construct(ProductSearchKeywordAnalyzerInterface $coreAnalyzer, TokenizerInterface $tokenizer)
    {
        $this->coreAnalyzer = $coreAnalyzer;
        $this->tokenizer = $tokenizer;
    }
    public function analyze(ProductEntity $product, Context $context): AnalyzedKeywordCollection
    {

        $keywords = $this->coreAnalyzer->analyze($product, $context);

        $description = $product->getTranslation('description');


        if ($description) {
            $tokens = $this->tokenizer->tokenize((string) $description);
            foreach ($tokens as $token) {
                $keywords->add(new AnalyzedKeyword((string) $token, 800));
            }
        }

        return $keywords;
    }
}