interface UserDto {
    id: string;
    name: string;
    email: string;
    photoUrl: string;
    socialProvider: string;
    socialProviderId: string;
    createdNew: boolean;
}

export default UserDto;
