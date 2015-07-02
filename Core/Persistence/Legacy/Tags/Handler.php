<?php

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Tags;

use Netgen\TagsBundle\SPI\Persistence\Tags\Handler as BaseTagsHandler;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag;

class Handler implements BaseTagsHandler
{
    /**
     * @var \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway
     */
    protected $gateway;

    /**
     * @var \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper
     */
    protected $mapper;

    /**
     * @param \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway $gateway
     * @param \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper $mapper
     */
    public function __construct( Gateway $gateway, Mapper $mapper )
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
    }

    /**
     * Loads a tag object from its $tagId
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     * @param string[] $translations
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function load( $tagId, array $translations = null )
    {
        $rows = $this->gateway->getFullTagData( $tagId, $translations );
        if ( empty( $rows ) )
        {
            throw new NotFoundException( "tag", $tagId );
        }

        $tag = $this->mapper->extractTagListFromRows( $rows );

        return reset( $tag );
    }

    /**
     * Loads a tag info object from its $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo
     */
    public function loadTagInfo( $tagId )
    {
        $row = $this->gateway->getBasicTagData( $tagId );
        return $this->mapper->createTagInfoFromRow( $row );
    }

    /**
     * Loads a tag object from its $remoteId
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param string $remoteId
     * @param string[] $translations
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function loadByRemoteId( $remoteId, array $translations = null )
    {
        $rows = $this->gateway->getFullTagDataByRemoteId( $remoteId, $translations );
        if ( empty( $rows ) )
        {
            throw new NotFoundException( "tag", $remoteId );
        }

        $tag = $this->mapper->extractTagListFromRows( $rows );

        return reset( $tag );
    }

    /**
     * Loads a tag info object from its remote ID
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param string $remoteId
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo
     */
    public function loadTagInfoByRemoteId( $remoteId )
    {
        $row = $this->gateway->getBasicTagDataByRemoteId( $remoteId );
        return $this->mapper->createTagInfoFromRow( $row );
    }

    /**
     * Loads a tag object from its URL
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param string $url
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag
     */
    public function loadByUrl( $url )
    {
        $data = $this->gateway->getBasicTagDataByUrl( $url );
        return $this->mapper->createTagFromRow( $data );
    }

    /**
     * Loads children of a tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all children starting at $offset are returned
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function loadChildren( $tagId, $offset = 0, $limit = -1 )
    {
        $tags = $this->gateway->getChildren( $tagId, $offset, $limit );
        return $this->mapper->extractTagListFromRows( $tags );
    }

    /**
     * Returns the number of children of a tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     *
     * @return int
     */
    public function getChildrenCount( $tagId )
    {
        return $this->gateway->getChildrenCount( $tagId );
    }

    /**
     * Loads tags with specified $keyword
     *
     * @param string $keyword
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all tags starting at $offset are returned
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function loadTagsByKeyword( $keyword, $offset = 0, $limit = -1 )
    {
        $tags = $this->gateway->getTagsByKeyword( $keyword, $offset, $limit );
        return $this->mapper->createTagsFromRows( $tags );
    }

    /**
     * Returns the number of tags with specified $keyword
     *
     * @param string $keyword
     *
     * @return int
     */
    public function getTagsByKeywordCount( $keyword )
    {
        return $this->gateway->getTagsByKeywordCount( $keyword );
    }

    /**
     * Loads the synonyms of a tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of tags returned. If $limit = -1 all synonyms starting at $offset are returned
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function loadSynonyms( $tagId, $offset = 0, $limit = -1 )
    {
        $tags = $this->gateway->getSynonyms( $tagId, $offset, $limit );
        return $this->mapper->extractTagListFromRows( $tags );
    }

    /**
     * Returns the number of synonyms of a tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     *
     * @return int
     */
    public function getSynonymCount( $tagId )
    {
        return $this->gateway->getSynonymCount( $tagId );
    }

    /**
     * Loads content IDs related to tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     * @param int $offset The start offset for paging
     * @param int $limit The number of content IDs returned. If $limit = -1 all content IDs starting at $offset are returned
     *
     * @return array
     */
    public function loadRelatedContentIds( $tagId, $offset = 0, $limit = -1 )
    {
        return $this->gateway->getRelatedContentIds( $tagId, $offset, $limit );
    }

    /**
     * Returns the number of content objects related to tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param mixed $tagId
     *
     * @return int
     */
    public function getRelatedContentCount( $tagId )
    {
        return $this->gateway->getRelatedContentCount( $tagId );
    }

    /**
     * Creates the new tag
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct $createStruct
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The newly created tag
     */
    public function create( CreateStruct $createStruct )
    {
        $parentTagData = null;
        if ( !empty( $createStruct->parentTagId ) )
        {
            $parentTagData = $this->gateway->getBasicTagData( $createStruct->parentTagId );
        }

        $newTagId = $this->gateway->create( $createStruct, $parentTagData );

        $newTag = $this->load( $newTagId );
        $this->updateSubtreeModificationTime( $newTag->id, $newTag->modificationDate );

        return $newTag;
    }

    /**
     * Updates tag identified by $tagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct $updateStruct
     * @param mixed $tagId
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The updated tag
     */
    public function update( UpdateStruct $updateStruct, $tagId )
    {
        $this->gateway->update( $updateStruct, $tagId );

        $this->updateSubtreeModificationTime( $tagId );
        return $this->load( $tagId );
    }

    /**
     * Creates a synonym
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct $createStruct
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The created synonym
     */
    public function addSynonym( $createStruct )
    {
        $mainTagData = $this->gateway->getBasicTagData( $createStruct->mainTagId );
        $newSynonymId = $this->gateway->createSynonym( $createStruct, $mainTagData );

        $newSynonym = $this->load( $newSynonymId );
        $this->updateSubtreeModificationTime( $newSynonym->id, $newSynonym->modificationDate );
        $this->updateSubtreeModificationTime( $createStruct->mainTagId, $newSynonym->modificationDate );

        return $newSynonym;
    }

    /**
     * Converts tag identified by $tagId to a synonym of tag identified by $mainTagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $tagId or $mainTagId are invalid
     *
     * @param mixed $tagId
     * @param mixed $mainTagId
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The converted synonym
     */
    public function convertToSynonym( $tagId, $mainTagId )
    {
        $tagData = $this->gateway->getBasicTagData( $tagId );
        $mainTagData = $this->gateway->getBasicTagData( $mainTagId );

        foreach ( $this->loadSynonyms( $tagId ) as $synonym )
        {
            $this->gateway->moveSynonym( $synonym->id, $mainTagData );
        }

        $this->gateway->convertToSynonym( $tagData["id"], $mainTagData );

        $convertedTag = $this->load( $tagId );
        if ( $tagData["parent_id"] > 0 )
        {
            $this->updateSubtreeModificationTime( $tagData["parent_id"], $convertedTag->modificationDate );
        }

        $this->updateSubtreeModificationTime( $mainTagData["id"], $convertedTag->modificationDate );

        return $convertedTag;
    }

    /**
     * Merges the tag identified by $tagId into the tag identified by $targetTagId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $tagId or $targetTagId are invalid
     *
     * @param mixed $tagId
     * @param mixed $targetTagId
     */
    public function merge( $tagId, $targetTagId )
    {
        $tagData = $this->gateway->getBasicTagData( $tagId );
        $targetTagData = $this->gateway->getBasicTagData( $targetTagId );

        foreach ( $this->loadSynonyms( $tagId ) as $synonym )
        {
            $this->gateway->transferTagAttributeLinks( $synonym->id, $targetTagId );
            $this->gateway->deleteTag( $synonym->id );
        }

        $this->gateway->transferTagAttributeLinks( $tagId, $targetTagId );
        $this->gateway->deleteTag( $tagId );

        $timestamp = time();
        if ( $tagData["parent_id"] > 0 )
        {
            $this->updateSubtreeModificationTime( $tagData["parent_id"], $timestamp );
        }

        $this->updateSubtreeModificationTime( $targetTagData["id"], $timestamp );
    }

    /**
     * Copies tag object identified by $sourceId into destination identified by $destinationParentId
     *
     * Also performs a copy of all child locations of $sourceId tag
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $sourceId or $destinationParentId are invalid
     *
     * @param mixed $sourceId The subtree denoted by the tag to copy
     * @param mixed $destinationParentId The target parent tag for the copy operation
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The newly created tag of the copied subtree
     */
    public function copySubtree( $sourceId, $destinationParentId )
    {
        $sourceTag = $this->load( $sourceId );
        $destinationParentTag = $this->load( $destinationParentId );

        $copiedTag = $this->recursiveCopySubtree( $sourceTag, $destinationParentTag );

        if ( $sourceTag->parentTagId > 0 )
        {
            $this->updateSubtreeModificationTime( $sourceTag->parentTagId, $copiedTag->modificationDate );
        }

        $this->updateSubtreeModificationTime( $copiedTag->id, $copiedTag->modificationDate );

        return $copiedTag;
    }

    /**
     * Copies tag object identified by $sourceData into destination identified by $destinationParentData
     *
     * Also performs a copy of all child locations of $sourceData tag
     *
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\Tag $sourceTag The subtree denoted by the tag to copy
     * @param \Netgen\TagsBundle\SPI\Persistence\Tags\Tag $destinationParentTag The target parent tag for the copy operation
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The newly created tag of the copied subtree
     */
    protected function recursiveCopySubtree( Tag $sourceTag, Tag $destinationParentTag )
    {
        // First copy the root node

        $createStruct = new CreateStruct();
        $createStruct->parentTagId = $destinationParentTag->id;
        $createStruct->keywords = $sourceTag->keywords;
        $createStruct->remoteId = md5( uniqid( get_class( $this ), true ) );
        $createStruct->alwaysAvailable = $sourceTag->alwaysAvailable;
        $createStruct->mainLanguageCode = $sourceTag->mainLanguageCode;

        $createdTag = $this->create( $createStruct );
        foreach ( $this->loadSynonyms( $sourceTag->id ) as $synonym )
        {
            $synonymCreateStruct = new SynonymCreateStruct();
            $synonymCreateStruct->keywords = $synonym->keywords;
            $synonymCreateStruct->remoteId = md5( uniqid( get_class( $this ), true ) );
            $synonymCreateStruct->mainTagId = $createdTag->id;
            $synonymCreateStruct->alwaysAvailable = $synonym->alwaysAvailable;
            $synonymCreateStruct->mainLanguageCode = $synonym->mainLanguageCode;

            $this->addSynonym( $synonymCreateStruct );
        }

        // Then copy the children
        $children = $this->loadChildren( $sourceTag->id );
        foreach ( $children as $child )
        {
            $this->recursiveCopySubtree(
                $child,
                $createdTag
            );
        }

        return $createdTag;
    }

    /**
     * Moves a tag identified by $sourceId into new parent identified by $destinationParentId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $sourceId or $destinationParentId are invalid
     *
     * @param mixed $sourceId
     * @param mixed $destinationParentId
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag The updated root tag of the moved subtree
     */
    public function moveSubtree( $sourceId, $destinationParentId )
    {
        $sourceTagData = $this->gateway->getBasicTagData( $sourceId );
        $destinationParentTagData = $this->gateway->getBasicTagData( $destinationParentId );

        $movedTagData = $this->gateway->moveSubtree( $sourceTagData, $destinationParentTagData );

        if ( $sourceTagData["parent_id"] > 0 )
        {
            $this->updateSubtreeModificationTime( $sourceTagData["parent_id"], $movedTagData["modified"] );
        }

        $this->updateSubtreeModificationTime( $movedTagData["id"], $movedTagData["modified"] );

        return $this->load( $movedTagData["id"] );
    }

    /**
     * Deletes tag identified by $tagId, including its synonyms and all tags under it
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified tag is not found
     *
     * If $tagId is a synonym, only the synonym is deleted
     *
     * @param mixed $tagId
     */
    public function deleteTag( $tagId )
    {
        $tagData = $this->gateway->getBasicTagData( $tagId );

        $this->gateway->deleteTag( $tagData["id"] );

        if ( $tagData["parent_tag_id"] > 0 )
        {
            $this->updateSubtreeModificationTime( $tagData["parent_tag_id"] );
        }
    }

    /**
     * Updated subtree modification time for tag and all its parents
     *
     * If tag is a synonym, subtree modification time of its main tag is updated
     *
     * @param mixed $tagId
     * @param int $timestamp
     */
    protected function updateSubtreeModificationTime( $tagId, $timestamp = null )
    {
        $tagData = $this->gateway->getBasicTagData( $tagId );
        $timestamp = $timestamp ?: time();

        $this->gateway->updateSubtreeModificationTime( $tagData["path_string"], $timestamp );

        if ( $tagData["main_tag_id"] > 0 )
        {
            $this->gateway->updateSubtreeModificationTime( (string)$tagData["main_tag_id"], $timestamp );
        }
    }
}
