export interface IUser {
  id: string;
  username: string;
  email: string;
  joinedAt: string;
}

export interface IPost {
  id: string;
  title: string;
  body: string;
  createdAt: string;
  updatedAt: string;
  author: string;
  author_username: string;
  author_email: string;
  author_joinedAt: string;
  category: string;
  category_title: string;
  category_description: string;
}
