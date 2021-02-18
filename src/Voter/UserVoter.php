<?php

	namespace App\Voter;

	use App\Entity\Customer;
	use App\Entity\User;
	use OpenApi\Tests\Fixtures\Parser\UserInterface;
	use Symfony\Component\HttpFoundation\Exception\JsonException;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
	use Symfony\Component\Security\Core\Authorization\Voter\Voter;

	class UserVoter extends Voter {

		const VIEW = "view";
		const UPDATE = "update";
		const DELETE = "delete";


		public function supports(string $attribute, $subject): bool
		{
			if(!in_array($attribute, [self::VIEW, self::UPDATE, self::DELETE])) {
				return false;
			}

			if(!$subject instanceof User) {
				return false;
			}

			return true;
		}

		public function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
		{
			$customer = $token->getUser();

			if(!$customer instanceof Customer) {
				return false;
			}

			$user = $subject;

			switch ($attribute) {
				case self::VIEW:
					return $this->canView($user, $customer);
				case self::UPDATE:
					return $this->canUpdate($user, $customer);
				case self::DELETE:
					return $this->canDelete($user, $customer);
			}

			return "You can't reach that";
		}

		public function canView(User $user, Customer $customer): bool
		{
			if($customer->getUsername() === $user->getCustomer()->getUsername()) {
				return true;
			}
			throw new JsonException("You don't have rights to do that. Please try again !", Response::HTTP_FORBIDDEN);
		}

		public function canUpdate(User $user, Customer $customer): bool
		{
			if($this->canView($user, $customer)) {
				return true;
			}
			throw new JsonException("You don't have rights to do that. Please try again !", Response::HTTP_FORBIDDEN);
		}

		public function canDelete(User $user, Customer $customer): bool
		{
			if($this->canView($user, $customer)) {
				return true;
			}
			throw new JsonException("You don't have rights to do that. Please try again !", Response::HTTP_FORBIDDEN);
		}
	}