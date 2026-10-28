import Link from '@docusaurus/Link';
import useBaseUrl from '@docusaurus/useBaseUrl';
import useDocusaurusContext from '@docusaurus/useDocusaurusContext';
import Layout from '@theme/Layout';
import CodeBlock from '@theme/CodeBlock';
import styles from './index.module.css';

const features = [
  {
    title: 'One API for every gateway',
    body: 'send(), sendOtp(), verify() and balance() work the same way on SMS EG, SMS Misr, Victory Link and Ooredoo.',
  },
  {
    title: 'OTP built in',
    body: 'Codes are generated with random_int() and checked with hash_equals(). SMS EG can also generate and check the code on the gateway.',
  },
  {
    title: 'Structured results',
    body: 'send() returns a ResponseCode enum, a message you can change in config, and the gateway’s own code and message.',
  },
  {
    title: 'Encoding detection',
    body: 'English, Arabic or Unicode encoding is picked from the message text, so the same code works in any language.',
  },
  {
    title: 'Timeouts and safe errors',
    body: 'Request and connect timeouts are configurable. Transport errors never include credentials, phone numbers or codes.',
  },
  {
    title: 'Custom drivers',
    body: 'Add your own gateway with Sms::extend(), or turn sending off in local and test environments with the null driver.',
  },
];

const example = `use Shabayek\\Sms\\Facade\\Sms;

$result = Sms::send('0120000000', 'Hello world');

$code = Sms::sendOtp('0120000000');`;

export default function Home() {
  const { siteConfig } = useDocusaurusContext();

  return (
    <Layout description={siteConfig.tagline}>
      <header className={styles.hero}>
        <div className="container">
          <img className={styles.symbol} src={useBaseUrl('/symbol.svg')} alt="" width="96" height="96" />
          <h1 className={styles.title}>
            Laravel <span>SMS</span>
          </h1>
          <p className={styles.tagline}>{siteConfig.tagline}</p>
          <div className={styles.actions}>
            <Link className="button button--primary button--lg" to="/docs/getting-started">
              Get started
            </Link>
            <Link className="button button--secondary button--outline button--lg" to="/docs/gateways">
              Supported gateways
            </Link>
          </div>
          <div className={styles.install}>
            <CodeBlock language="bash">composer require shabayek/laravel-sms</CodeBlock>
          </div>
        </div>
      </header>

      <main className="container">
        <section className={styles.features}>
          {features.map((feature) => (
            <div key={feature.title} className={styles.feature}>
              <h2>{feature.title}</h2>
              <p>{feature.body}</p>
            </div>
          ))}
        </section>

        <section className={styles.example}>
          <h2>Send a message</h2>
          <CodeBlock language="php">{example}</CodeBlock>
          <Link to="/docs/getting-started#quick-start">Read the quick start →</Link>
        </section>
      </main>
    </Layout>
  );
}
