<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Tests\Helper;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * @group tty
 */
class QuestionHelperTest extends AbstractQuestionHelperTest
{
	public function testAskChoice()
	{
		$questionHelper = new QuestionHelper();

		$helperSet = new HelperSet([new FormatterHelper()]);
		$questionHelper->setHelperSet($helperSet);

		$heroes = ['Superman', 'Batman', 'Spiderman'];

		$inputStream = $this->getInputStream("\n1\n  1  \nFabien\n1\nFabien\n1\n0,2\n 0 , 2  \n\n\n");

		$question = new ChoiceQuestion('What is your favorite superhero?', $heroes, '2');
		$question->setMaxAttempts(1);
		// first answer is an empty answer, we're supposed to receive the default value
		$this->assertSame('Spiderman', $questionHelper->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));

		$question = new ChoiceQuestion('What is your favorite superhero?', $heroes);
		$question->setMaxAttempts(1);
		$this->assertSame('Batman', $questionHelper->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('Batman', $questionHelper->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));

		$question = new ChoiceQuestion('What is your favorite superhero?', $heroes);
		$question->setErrorMessage('Input "%s" is not a superhero!');
		$question->setMaxAttempts(2);
		$this->assertSame('Batman', $questionHelper->ask($this->createStreamableInputInterfaceMock($inputStream), $output = $this->createOutputInterface(), $question));

		rewind($output->getStream());
		$stream = stream_get_contents($output->getStream());
		$this->assertContains('Input "Fabien" is not a superhero!', $stream);

		try {
			$question = new ChoiceQuestion('What is your favorite superhero?', $heroes, '1');
			$question->setMaxAttempts(1);
			$questionHelper->ask($this->createStreamableInputInterfaceMock($inputStream), $output = $this->createOutputInterface(), $question);
			$this->fail();
		} catch (\InvalidArgumentException $e) {
			$this->assertSame('Value "Fabien" is invalid', $e->getMessage());
		}

		$question = new ChoiceQuestion('What is your favorite superhero?', $heroes, null);
		$question->setMaxAttempts(1);
		$question->setMultiselect(true);

		$this->assertSame(['Batman'], $questionHelper->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame(['Superman', 'Spiderman'], $questionHelper->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame(['Superman', 'Spiderman'], $questionHelper->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));

		$question = new ChoiceQuestion('What is your favorite superhero?', $heroes, '0,1');
		$question->setMaxAttempts(1);
		$question->setMultiselect(true);

		$this->assertSame(['Superman', 'Batman'], $questionHelper->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));

		$question = new ChoiceQuestion('What is your favorite superhero?', $heroes, ' 0 , 1 ');
		$question->setMaxAttempts(1);
		$question->setMultiselect(true);

		$this->assertSame(['Superman', 'Batman'], $questionHelper->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));

		$question = new ChoiceQuestion('What is your favorite superhero?', $heroes, 0);
		// We are supposed to get the default value since we are not in interactive mode
		$this->assertSame('Superman', $questionHelper->ask($this->createStreamableInputInterfaceMock($inputStream, true), $this->createOutputInterface(), $question));
	}

	public function testAsk()
	{
		$dialog = new QuestionHelper();

		$inputStream = $this->getInputStream("\n8AM\n");

		$question = new Question('What time is it?', '2PM');
		$this->assertSame('2PM', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));

		$question = new Question('What time is it?', '2PM');
		$this->assertSame('8AM', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $output = $this->createOutputInterface(), $question));

		rewind($output->getStream());
		$this->assertSame('What time is it?', stream_get_contents($output->getStream()));
	}

	public function testAskWithAutocomplete()
	{
		if (!$this->hasSttyAvailable()) {
			$this->markTestSkipped('`stty` is required to test autocomplete functionality');
		}

		// Acm<NEWLINE>
		// Ac<BACKSPACE><BACKSPACE>s<TAB>Test<NEWLINE>
		// <NEWLINE>
		// <UP ARROW><UP ARROW><NEWLINE>
		// <UP ARROW><UP ARROW><UP ARROW><UP ARROW><UP ARROW><TAB>Test<NEWLINE>
		// <DOWN ARROW><NEWLINE>
		// S<BACKSPACE><BACKSPACE><DOWN ARROW><DOWN ARROW><NEWLINE>
		// F00<BACKSPACE><BACKSPACE>oo<TAB><NEWLINE>
		$inputStream = $this->getInputStream("Acm\nAc\177\177s\tTest\n\n\033[A\033[A\n\033[A\033[A\033[A\033[A\033[A\tTest\n\033[B\nS\177\177\033[B\033[B\nF00\177\177oo\t\n");

		$dialog = new QuestionHelper();
		$helperSet = new HelperSet([new FormatterHelper()]);
		$dialog->setHelperSet($helperSet);

		$question = new Question('Please select a bundle', 'FrameworkBundle');
		$question->setAutocompleterValues(['AcmeDemoBundle', 'AsseticBundle', 'SecurityBundle', 'FooBundle']);

		$this->assertSame('AcmeDemoBundle', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('AsseticBundleTest', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('FrameworkBundle', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('SecurityBundle', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('FooBundleTest', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('AcmeDemoBundle', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('AsseticBundle', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('FooBundle', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
	}

	public function testAskWithAutocompleteWithNonSequentialKeys()
	{
		if (!$this->hasSttyAvailable()) {
			$this->markTestSkipped('`stty` is required to test autocomplete functionality');
		}

		// <UP ARROW><UP ARROW><NEWLINE><DOWN ARROW><DOWN ARROW><NEWLINE>
		$inputStream = $this->getInputStream("\033[A\033[A\n\033[B\033[B\n");

		$dialog = new QuestionHelper();
		$dialog->setHelperSet(new HelperSet([new FormatterHelper()]));

		$question = new ChoiceQuestion('Please select a bundle', [1 => 'AcmeDemoBundle', 4 => 'AsseticBundle']);
		$question->setMaxAttempts(1);

		$this->assertSame('AcmeDemoBundle', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('AsseticBundle', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
	}

	public function testAskWithAutocompleteWithExactMatch()
	{
		if (!$this->hasSttyAvailable()) {
			$this->markTestSkipped('`stty` is required to test autocomplete functionality');
		}

		$inputStream = $this->getInputStream("b\n");

		$possibleChoices = [
			'a' => 'berlin',
			'b' => 'copenhagen',
			'c' => 'amsterdam',
		];

		$dialog = new QuestionHelper();
		$dialog->setHelperSet(new HelperSet([new FormatterHelper()]));

		$question = new ChoiceQuestion('Please select a city', $possibleChoices);
		$question->setMaxAttempts(1);

		$this->assertSame('b', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
	}

	public function testAutocompleteWithTrailingBackslash()
	{
		if (!$this->hasSttyAvailable()) {
			$this->markTestSkipped('`stty` is required to test autocomplete functionality');
		}

		$inputStream = $this->getInputStream('E');

		$dialog = new QuestionHelper();
		$helperSet = new HelperSet([new FormatterHelper()]);
		$dialog->setHelperSet($helperSet);

		$question = new Question('');
		$expectedCompletion = 'ExampleNamespace\\';
		$question->setAutocompleterValues([$expectedCompletion]);

		$output = $this->createOutputInterface();
		$dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $output, $question);

		$outputStream = $output->getStream();
		rewind($outputStream);
		$actualOutput = stream_get_contents($outputStream);

		// Shell control (esc) sequences are not so important: we only care that
		// <hl> tag is interpreted correctly and replaced
		$irrelevantEscSequences = [
			"\0337" => '', // Save cursor position
			"\0338" => '', // Restore cursor position
			"\033[K" => '', // Clear line from cursor till the end
		];

		$importantActualOutput = strtr($actualOutput, $irrelevantEscSequences);

		// Remove colors (e.g. "\033[30m", "\033[31;41m")
		$importantActualOutput = preg_replace('/\033\[\d+(;\d+)?m/', '', $importantActualOutput);

		$this->assertSame($expectedCompletion, $importantActualOutput);
	}

	public function testAskHiddenResponse()
	{
		if ('\\' === DIRECTORY_SEPARATOR) {
			$this->markTestSkipped('This test is not supported on Windows');
		}

		$dialog = new QuestionHelper();

		$question = new Question('What time is it?');
		$question->setHidden(true);

		$this->assertSame('8AM', $dialog->ask($this->createStreamableInputInterfaceMock($this->getInputStream("8AM\n")), $this->createOutputInterface(), $question));
	}

	/**
	 * @dataProvider getAskConfirmationData
	 */
	public function testAskConfirmation($question, $expected, $default = true)
	{
		$dialog = new QuestionHelper();

		$inputStream = $this->getInputStream($question . "\n");
		$question = new ConfirmationQuestion('Do you like French fries?', $default);
		$this->assertSame($expected, $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question), 'confirmation question should ' . ($expected ? 'pass' : 'cancel'));
	}

	public function getAskConfirmationData()
	{
		return [
			['', true],
			['', false, false],
			['y', true],
			['yes', true],
			['n', false],
			['no', false],
		];
	}

	public function testAskConfirmationWithCustomTrueAnswer()
	{
		$dialog = new QuestionHelper();

		$inputStream = $this->getInputStream("j\ny\n");
		$question = new ConfirmationQuestion('Do you like French fries?', false, '/^(j|y)/i');
		$this->assertTrue($dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$question = new ConfirmationQuestion('Do you like French fries?', false, '/^(j|y)/i');
		$this->assertTrue($dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
	}

	public function testAskAndValidate()
	{
		$dialog = new QuestionHelper();
		$helperSet = new HelperSet([new FormatterHelper()]);
		$dialog->setHelperSet($helperSet);

		$error = 'This is not a color!';
		$validator = function ($color) use ($error) {
			if (!in_array($color, ['white', 'black'])) {
				throw new \InvalidArgumentException($error);
			}

			return $color;
		};

		$question = new Question('What color was the white horse of Henry IV?', 'white');
		$question->setValidator($validator);
		$question->setMaxAttempts(2);

		$inputStream = $this->getInputStream("\nblack\n");
		$this->assertSame('white', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('black', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));

		try {
			$dialog->ask($this->createStreamableInputInterfaceMock($this->getInputStream("green\nyellow\norange\n")), $this->createOutputInterface(), $question);
			$this->fail();
		} catch (\InvalidArgumentException $e) {
			$this->assertSame($error, $e->getMessage());
		}
	}

	/**
	 * @dataProvider simpleAnswerProvider
	 */
	public function testSelectChoiceFromSimpleChoices($providedAnswer, $expectedValue)
	{
		$possibleChoices = [
			'My environment 1',
			'My environment 2',
			'My environment 3',
		];

		$dialog = new QuestionHelper();
		$helperSet = new HelperSet([new FormatterHelper()]);
		$dialog->setHelperSet($helperSet);

		$question = new ChoiceQuestion('Please select the environment to load', $possibleChoices);
		$question->setMaxAttempts(1);
		$answer = $dialog->ask($this->createStreamableInputInterfaceMock($this->getInputStream($providedAnswer . "\n")), $this->createOutputInterface(), $question);

		$this->assertSame($expectedValue, $answer);
	}

	public function simpleAnswerProvider()
	{
		return [
			[0, 'My environment 1'],
			[1, 'My environment 2'],
			[2, 'My environment 3'],
			['My environment 1', 'My environment 1'],
			['My environment 2', 'My environment 2'],
			['My environment 3', 'My environment 3'],
		];
	}

	/**
	 * @dataProvider specialCharacterInMultipleChoice
	 */
	public function testSpecialCharacterChoiceFromMultipleChoiceList($providedAnswer, $expectedValue)
	{
		$possibleChoices = [
			'.',
			'src',
		];

		$dialog = new QuestionHelper();
		$inputStream = $this->getInputStream($providedAnswer . "\n");
		$helperSet = new HelperSet([new FormatterHelper()]);
		$dialog->setHelperSet($helperSet);

		$question = new ChoiceQuestion('Please select the directory', $possibleChoices);
		$question->setMaxAttempts(1);
		$question->setMultiselect(true);
		$answer = $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question);

		$this->assertSame($expectedValue, $answer);
	}

	public function specialCharacterInMultipleChoice()
	{
		return [
			['.', ['.']],
			['., src', ['.', 'src']],
		];
	}

	/**
	 * @dataProvider mixedKeysChoiceListAnswerProvider
	 */
	public function testChoiceFromChoicelistWithMixedKeys($providedAnswer, $expectedValue)
	{
		$possibleChoices = [
			'0' => 'No environment',
			'1' => 'My environment 1',
			'env_2' => 'My environment 2',
			3 => 'My environment 3',
		];

		$dialog = new QuestionHelper();
		$helperSet = new HelperSet([new FormatterHelper()]);
		$dialog->setHelperSet($helperSet);

		$question = new ChoiceQuestion('Please select the environment to load', $possibleChoices);
		$question->setMaxAttempts(1);
		$answer = $dialog->ask($this->createStreamableInputInterfaceMock($this->getInputStream($providedAnswer . "\n")), $this->createOutputInterface(), $question);

		$this->assertSame($expectedValue, $answer);
	}

	public function mixedKeysChoiceListAnswerProvider()
	{
		return [
			['0', '0'],
			['No environment', '0'],
			['1', '1'],
			['env_2', 'env_2'],
			[3, '3'],
			['My environment 1', '1'],
		];
	}

	/**
	 * @dataProvider answerProvider
	 */
	public function testSelectChoiceFromChoiceList($providedAnswer, $expectedValue)
	{
		$possibleChoices = [
			'env_1' => 'My environment 1',
			'env_2' => 'My environment',
			'env_3' => 'My environment',
		];

		$dialog = new QuestionHelper();
		$helperSet = new HelperSet([new FormatterHelper()]);
		$dialog->setHelperSet($helperSet);

		$question = new ChoiceQuestion('Please select the environment to load', $possibleChoices);
		$question->setMaxAttempts(1);
		$answer = $dialog->ask($this->createStreamableInputInterfaceMock($this->getInputStream($providedAnswer . "\n")), $this->createOutputInterface(), $question);

		$this->assertSame($expectedValue, $answer);
	}

	/**
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage The provided answer is ambiguous. Value should be one of env_2 or env_3.
	 */
	public function testAmbiguousChoiceFromChoicelist()
	{
		$possibleChoices = [
			'env_1' => 'My first environment',
			'env_2' => 'My environment',
			'env_3' => 'My environment',
		];

		$dialog = new QuestionHelper();
		$helperSet = new HelperSet([new FormatterHelper()]);
		$dialog->setHelperSet($helperSet);

		$question = new ChoiceQuestion('Please select the environment to load', $possibleChoices);
		$question->setMaxAttempts(1);

		$dialog->ask($this->createStreamableInputInterfaceMock($this->getInputStream("My environment\n")), $this->createOutputInterface(), $question);
	}

	public function answerProvider()
	{
		return [
			['env_1', 'env_1'],
			['env_2', 'env_2'],
			['env_3', 'env_3'],
			['My environment 1', 'env_1'],
		];
	}

	public function testNoInteraction()
	{
		$dialog = new QuestionHelper();
		$question = new Question('Do you have a job?', 'not yet');
		$this->assertSame('not yet', $dialog->ask($this->createStreamableInputInterfaceMock(null, false), $this->createOutputInterface(), $question));
	}

	/**
	 * @requires function mb_strwidth
	 */
	public function testChoiceOutputFormattingQuestionForUtf8Keys()
	{
		$question = 'Lorem ipsum?';
		$possibleChoices = [
			'foo' => 'foo',
			'żółw' => 'bar',
			'łabądź' => 'baz',
		];
		$outputShown = [
			$question,
			'  [<info>foo   </info>] foo',
			'  [<info>żółw  </info>] bar',
			'  [<info>łabądź</info>] baz',
		];
		$output = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')->getMock();
		$output->method('getFormatter')->willReturn(new OutputFormatter());

		$dialog = new QuestionHelper();
		$helperSet = new HelperSet([new FormatterHelper()]);
		$dialog->setHelperSet($helperSet);

		$output->expects($this->once())->method('writeln')->with($this->equalTo($outputShown));

		$question = new ChoiceQuestion($question, $possibleChoices, 'foo');
		$dialog->ask($this->createStreamableInputInterfaceMock($this->getInputStream("\n")), $output, $question);
	}

	/**
	 * @expectedException        \Symfony\Component\Console\Exception\RuntimeException
	 * @expectedExceptionMessage Aborted
	 */
	public function testAskThrowsExceptionOnMissingInput()
	{
		$dialog = new QuestionHelper();
		$dialog->ask($this->createStreamableInputInterfaceMock($this->getInputStream('')), $this->createOutputInterface(), new Question('What\'s your name?'));
	}

	/**
	 * @expectedException        \Symfony\Component\Console\Exception\RuntimeException
	 * @expectedExceptionMessage Aborted
	 */
	public function testAskThrowsExceptionOnMissingInputWithValidator()
	{
		$dialog = new QuestionHelper();

		$question = new Question('What\'s your name?');
		$question->setValidator(function () {
			if (!$value) {
				throw new \Exception('A value is required.');
			}
		});

		$dialog->ask($this->createStreamableInputInterfaceMock($this->getInputStream('')), $this->createOutputInterface(), $question);
	}

	/**
	 * @expectedException \LogicException
	 * @expectedExceptionMessage Choice question must have at least 1 choice available.
	 */
	public function testEmptyChoices()
	{
		new ChoiceQuestion('Question', [], 'irrelevant');
	}

	public function testTraversableAutocomplete()
	{
		if (!$this->hasSttyAvailable()) {
			$this->markTestSkipped('`stty` is required to test autocomplete functionality');
		}

		// Acm<NEWLINE>
		// Ac<BACKSPACE><BACKSPACE>s<TAB>Test<NEWLINE>
		// <NEWLINE>
		// <UP ARROW><UP ARROW><NEWLINE>
		// <UP ARROW><UP ARROW><UP ARROW><UP ARROW><UP ARROW><TAB>Test<NEWLINE>
		// <DOWN ARROW><NEWLINE>
		// S<BACKSPACE><BACKSPACE><DOWN ARROW><DOWN ARROW><NEWLINE>
		// F00<BACKSPACE><BACKSPACE>oo<TAB><NEWLINE>
		$inputStream = $this->getInputStream("Acm\nAc\177\177s\tTest\n\n\033[A\033[A\n\033[A\033[A\033[A\033[A\033[A\tTest\n\033[B\nS\177\177\033[B\033[B\nF00\177\177oo\t\n");

		$dialog = new QuestionHelper();
		$helperSet = new HelperSet([new FormatterHelper()]);
		$dialog->setHelperSet($helperSet);

		$question = new Question('Please select a bundle', 'FrameworkBundle');
		$question->setAutocompleterValues(new AutocompleteValues(['irrelevant' => 'AcmeDemoBundle', 'AsseticBundle', 'SecurityBundle', 'FooBundle']));

		$this->assertSame('AcmeDemoBundle', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('AsseticBundleTest', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('FrameworkBundle', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('SecurityBundle', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('FooBundleTest', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('AcmeDemoBundle', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('AsseticBundle', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
		$this->assertSame('FooBundle', $dialog->ask($this->createStreamableInputInterfaceMock($inputStream), $this->createOutputInterface(), $question));
	}

	protected function getInputStream($input)
	{
		$stream = fopen('php://memory', 'r+', false);
		fwrite($stream, $input);
		rewind($stream);

		return $stream;
	}

	protected function createOutputInterface()
	{
		return new StreamOutput(fopen('php://memory', 'r+', false));
	}

	protected function createInputInterfaceMock($interactive = true)
	{
		$mock = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')->getMock();
		$mock->expects($this->any())
			->method('isInteractive')
			->will($this->returnValue($interactive));

		return $mock;
	}

	private function hasSttyAvailable()
	{
		exec('stty 2>&1', $output, $exitcode);

		return 0 === $exitcode;
	}
}

class AutocompleteValues implements \IteratorAggregate
{
	private $values;

	public function __construct(array $values)
	{
		$this->values = $values;
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->values);
	}
}