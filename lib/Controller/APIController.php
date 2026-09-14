<?php
namespace OCA\GroupFolderTags\Controller;

use OCP\AppFramework\OCSController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\IRequest;

use OCA\GroupFolders\Folder\FolderManager;
use OCA\GroupfolderTags\Errors\TagNotFound;
use OCA\GroupfolderTags\Service\TagService;
use OCA\GroupfolderTags\Db\TagMapper;


class APIController extends OCSController {

    public function __construct(
        string $appName,
        IRequest $request,
        protected readonly FolderManager $folderManager,
        protected readonly TagService $service,
        protected readonly TagMapper $mapper
    ) {
        parent::__construct($appName, $request);
    }

    /**
     * Get all groupfolders with the specific tagKey.
     *
     * @param string tagKey The tagKey to search for
     * @param string|null tagValue Optional search for only tagKey with that value
     * @return DataResponse<Http::STATUS_OK, array{groupfolders: array}>
     *
     * 200: returns 0 or many matching tags
     * 400: missing mandatory tagKey parameter
     */
    #[ApiRoute(verb: 'GET', url: '/api/v1/groupfolders/{tagKey}')]
    public function findAllWithTagKey(string $tagKey, ?string $tagValue = null): DataResponse {
        if (is_null($tagKey) || $tagKey === "") {
            return new DataResponse(['error' => 'No tag key provided'], 400);
        }

        $tags = $this->mapper->findAll($tagKey, $tagValue);
        return new DataResponse(['tags' => $tags], 200);
    }

    /**
     * Get all tags of the groupfolder with the specified id
     *
     * @param int groupFolderId The id of the groupfolder to search for
     * @param string|null tagKey Optional return only the tag with the specified tagKey
     * @return DataResponse<Http::STATUS_OK, array{tags: Tag[]}>
     *
     * 200: returns 0 or many matching tags
     * 400: invalid tag, when looking for a specific tagKey
     */
    #[ApiRoute(verb: 'GET', url: '/api/v1/groupfolder/{groupFolderId}')]
    public function getGroupfolderTag(int $groupFolderId, ?string $tagKey = null): DataResponse {
        try {
            $tags = $this->service->findByGroupFolderAndKey($groupFolderId, $tagKey);
            return new DataResponse(['tags' => $tags], 200);
        } catch (TagNotFound $e) {
            return new DataResponse(['error' => "Tag {$tagKey} not found!"], 400);
        }
    }

    /**
     * set the tagKey of the specified groupfolder to tagValue
     *
     * @param int groupFolderId The id of the groupfolder to set the tag
     * @param string tagKey The key to set
     * @param string tagValue The value to set
     * @return DataResponse<Http::STATUS_OK, array{tag: Tag}>
     *
     * 200: a tag key
     * 400: missing tagKey or tagValue parameter
     * 404: groupfolder with groupFolderId does not exist
     */
    #[ApiRoute(verb: 'POST', url: '/api/v1/groupfolder/{groupFolderId}/{tagKey}')]
    public function setGroupfolderTag(int $groupFolderId, string $tagKey, string $tagValue): DataResponse {
        if (is_null($tagKey) || $tagKey === "") {
            return new DataResponse(['error' => 'No tag key provided!'], 400);
        }
        if (is_null($tagValue) || $tagValue === "") {
            return new DataResponse(['error' => 'No tag value provided!'], 400);
        }

        $folder = $this->folderManager->getFolder($groupFolderId);
		if ($folder === null) {
		    return new DataResponse(['error' => 'Groupfolder does not exist!'], 404);
        }

        $tag = $this->service->update($groupFolderId, $tagKey, $tagValue);
        return new DataResponse(['tag' => $tag], 200);
    }

    /**
     * remove the tagKey of the specified groupfolder
     *
     * @param int groupFolderId The id of the groupfolder to remove the tag from
     * @param string tagKey The key to remove
     * @return DataResponse<Http::STATUS_OK, array{tag: Tag}>
     */
    #[ApiRoute(verb: 'DELETE', url: '/api/v1/groupfolder/{groupFolderId}/{tagKey}')]
    public function removeGroupfolderTag(int $groupFolderId, string $tagKey): DataResponse {
        if (is_null($tagKey) || $tagKey === "") {
            return new DataResponse(['error' => 'no tag key provided'], 400);
        }

        try {
            $tag = $this->service->delete((int)$groupFolderId, $tagKey);
            return new DataResponse(['tag' => $tag], 200);
        } catch (TagNotFound $e) {
            return new DataResponse(['error' => "Tag {$tagKey} not found!"], 400);
        }
    }
}
