import React from 'react';
import { useQuery } from 'react-query';
import { Link } from 'react-router-dom';
import { IPost } from '../types';
import axiosInstance from '../utils/axiosInstance';

function usePosts(): any {
  return useQuery('posts', async () => {
    const { data } = await axiosInstance.get('post/read.php');
    return data;
  });
}

const Home: React.FC = () => {
  const { status, data, error } = usePosts();

  return (
    <section className="text-gray-600 body-font overflow-hidden">
      <div className="container px-5 py-24 mx-auto">
        <div className="-my-8 divide-y-2 divide-gray-100">
          {status === 'loading' ? (
            <h1>loading...</h1>
          ) : status === 'error' ? (
            <h1>{error.message}</h1>
          ) : (
            data &&
            data.records.map((post: IPost) => (
              <div className="py-8 flex flex-wrap md:flex-nowrap" key={post.id}>
                <div className="md:w-64 md:mb-0 mb-6 flex-shrink-0 flex flex-col">
                  <span className="font-semibold title-font text-gray-700">
                    {post.category_title}
                  </span>
                  <span className="mt-1 text-gray-500 text-sm">
                    {new Date(post.createdAt).toLocaleString()}
                  </span>
                </div>
                <div className="md:flex-grow">
                  <h2 className="text-2xl font-medium text-gray-900 title-font mb-2">
                    {post.title}
                  </h2>
                  <p className="leading-relaxed">{post.body.slice(0, 100)}</p>
                  <Link
                    to={`/post/${post.id}`}
                    className="text-indigo-500 inline-flex items-center mt-4"
                  >
                    Learn More
                    <svg
                      className="w-4 h-4 ml-2"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      strokeWidth="2"
                      fill="none"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    >
                      <path d="M5 12h14"></path>
                      <path d="M12 5l7 7-7 7"></path>
                    </svg>
                  </Link>
                </div>
              </div>
            ))
          )}
        </div>
      </div>
    </section>
  );
};

export default Home;
